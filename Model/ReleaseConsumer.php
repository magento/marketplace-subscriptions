<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionReleaseInterface;
use PayPal\Subscription\Api\Data\SubscriptionReleaseInterfaceFactory;
use PayPal\Subscription\Api\ReleaseConsumerInterface;
use PayPal\Subscription\Api\SubscriptionManagementInterface;
use PayPal\Subscription\Model\Email\Release as ReleaseEmail;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease as SubscriptionReleaseResource;
use Psr\Log\LoggerInterface;

/**
 * Consumer for Release Message Queue.
 */
class ReleaseConsumer implements ReleaseConsumerInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var SubscriptionReleaseInterfaceFactory
     */
    private $subscriptionReleaseInterfaceFactory;

    /**
     * @var ResourceModel\SubscriptionRelease
     */
    private $subscriptionReleaseResource;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReleaseEmail
     */
    private $releaseEmail;

    /**
     * @var SubscriptionManagementInterface
     */
    private $subscriptionManagement;

    /**
     * @var CreateSubscriptionQuoteInterface
     */
    private $createSubscriptionQuote;

    /**
     * @var CreateSubscriptionOrderInterface
     */
    private $createSubscriptionOrder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * ReleaseConsumer constructor
     *
     * @param ConfigurationInterface $configuration
     * @param SubscriptionReleaseInterfaceFactory $subscriptionReleaseInterfaceFactory
     * @param SubscriptionReleaseResource $subscriptionReleaseResource
     * @param SubscriptionResource $subscriptionResource
     * @param SubscriptionManagementInterface $subscriptionManagement
     * @param LoggerInterface $logger
     * @param ReleaseEmail $releaseEmail
     * @param CreateSubscriptionQuoteInterface $createSubscriptionQuote
     * @param CreateSubscriptionOrderInterface $createSubscriptionOrder
     * @param State $appState
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ConfigurationInterface $configuration,
        SubscriptionReleaseInterfaceFactory $subscriptionReleaseInterfaceFactory,
        SubscriptionReleaseResource $subscriptionReleaseResource,
        SubscriptionResource $subscriptionResource,
        SubscriptionManagementInterface $subscriptionManagement,
        LoggerInterface $logger,
        ReleaseEmail $releaseEmail,
        CreateSubscriptionQuoteInterface $createSubscriptionQuote,
        CreateSubscriptionOrderInterface $createSubscriptionOrder,
        State $appState,
        CustomerRepositoryInterface $customerRepository,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
        $this->configuration = $configuration;
        $this->subscriptionReleaseInterfaceFactory = $subscriptionReleaseInterfaceFactory;
        $this->subscriptionReleaseResource = $subscriptionReleaseResource;
        $this->subscriptionResource = $subscriptionResource;
        $this->subscriptionManagement = $subscriptionManagement;
        $this->logger = $logger;
        $this->createSubscriptionQuote = $createSubscriptionQuote;
        $this->createSubscriptionOrder = $createSubscriptionOrder;
        $this->releaseEmail = $releaseEmail;
        $this->appState = $appState;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Process subscription release
     *
     * @param SubscriptionInterface $subscription
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        SubscriptionInterface $subscription
    ): void {
        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
        }
        $quote = null;
        $errorMessage = null;
        $originalOrderId = $subscription->getOrderId();
        try {
            $originalStockFailures = (int) $subscription->getStockFailures();
            $originalFailedPayments = (int) $subscription->getFailedPayments();
            /** @var CartInterface|Quote $quote */
            try {
                $quote = $this->createSubscriptionQuote->execute($subscription);
            } catch (\Exception $e) {
                if ($originalStockFailures == (int) $subscription->getStockFailures() &&
                    $originalFailedPayments == (int) $subscription->getFailedPayments()) {
                    $subscription->setFailedPayments(
                        $subscription->getFailedPayments()+1
                    );
                }
                throw new LocalizedException(__($e->getMessage()));
            }
            /** @var OrderInterface|Order $order */
            $order = $this->createSubscriptionOrder->execute(
                $quote,
                $subscription
            );
            $priceChanged = (bool) $subscription->getData('price_changed');
            if ($priceChanged === true) {
                // Update original order ID with latest order.
                $subscription->setOrderId((int) $order->getId());
            }
            $this->createRelease(
                $subscription,
                $order
            );
            if ($this->configuration->getReleaseReminderEmailTiming() === 0) {
                $this->releaseEmail->success(
                    $quote->getCustomer(),
                    $subscription
                );
            }
            if ($priceChanged === true) {
                $this->releaseEmail->priceChanged(
                    $quote->getCustomer(),
                    $subscription,
                    $this->orderRepository->get($originalOrderId),
                    $order
                );
            }
        } catch (LocalizedException|CommandException $exception) {
            $this->handleLocalizedAndCommandExceptionCatch(
                $exception,
                $subscription,
                $originalStockFailures,
                $originalFailedPayments
            );
        } catch (\Exception $e) {
            $errorMessage = 'Subscription Release Error - ' . $e->getMessage();
            $this->logger->error($errorMessage);
        }
        if ($errorMessage !== null &&
            $this->configuration->getErrorLoggingEmailsEnabled()) {
            $this->releaseEmail->failureAdmin(
                $errorMessage,
                $subscription
            );
        }
    }

    /**
     * Handle Localized and Command exception catch
     *
     * @param LocalizedException|CommandException $exception
     * @param SubscriptionInterface $subscription
     * @param int $originalStockFailures
     * @param int $originalFailedPayments
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function handleLocalizedAndCommandExceptionCatch(
        LocalizedException|CommandException $exception,
        SubscriptionInterface $subscription,
        int $originalStockFailures,
        int $originalFailedPayments
    ): void {
        $errorMessage = $exception->getMessage();
        $sendFailureToCustomer = false;
        if ($originalStockFailures >= $subscription->getStockFailures() &&
            $originalFailedPayments >= $subscription->getFailedPayments()) {
            $this->subscriptionManagement->changeStatus(
                $subscription->getCustomerId(),
                $subscription->getId(),
                SubscriptionInterface::STATUS_CANCELLED
            );
            $subscription->setStatus(
                SubscriptionInterface::STATUS_CANCELLED
            );
            $subscription->addHistory(
                "Release",
                "customer",
                "Subscription automatically paused: " . $e->getMessage(),
                true,
                false
            );
        } else {
            $pauseSubscription = false;
            if ($originalStockFailures < $subscription->getStockFailures()) {
                $errorMessage = 'Product Out of Stock';
                $sendFailureToCustomer = true;
                $stockFailureLimit = $this->configuration->getStockFailuresAllowed();
                if ($stockFailureLimit != null) {
                    $resetAndCancel = $subscription->getStockFailures() >= $stockFailureLimit;
                    if ($resetAndCancel === true) {
                        $pauseSubscription = true;
                        $subscription->setStockFailures(0);
                        $errorMessage .= ' - Limit reached';
                    }
                }
            }
            if ($originalFailedPayments < $subscription->getFailedPayments()) {
                $errorMessage = 'Failed Payment';
                $sendFailureToCustomer = true;
                $failedPaymentLimit = $this->configuration->getFailedPaymentsAllowed();
                if ($failedPaymentLimit != null) {
                    $resetAndCancel = $subscription->getFailedPayments() >= $failedPaymentLimit;
                    if ($resetAndCancel === true) {
                        $subscription->setFailedPayments(0);
                        if ($pauseSubscription !== true) {
                            $pauseSubscription = true;
                            $errorMessage .= ' - Limit reached';
                        }
                    }
                }
            }
            if ($pauseSubscription === true) {
                $this->subscriptionManagement->changeStatus(
                    $subscription->getCustomerId(),
                    $subscription->getId(),
                    SubscriptionInterface::STATUS_CANCELLED
                );
                $subscription->setStatus(
                    SubscriptionInterface::STATUS_CANCELLED
                );
                $subscription->addHistory(
                    "Release",
                    "customer",
                    "Subscription automatically paused: " . $errorMessage,
                    true,
                    false
                );
            } else {
                $oldNextReleaseDate = $subscription->getNextReleaseDate();
                $subscription->setNextReleaseDate(
                    date(
                        'Y-m-d H:i:s',
                        strtotime($oldNextReleaseDate . ' +1 day')
                    )
                );
                $subscription->addHistory(
                    "Release",
                    "customer",
                    "Subscription Release Failed: " . $errorMessage,
                    true,
                    false
                );
            }
        }
        $this->subscriptionResource->save($subscription);
        if ($sendFailureToCustomer === true) {
            $customerId = $subscription->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $this->releaseEmail->failure(
                $customer,
                $subscription,
                $errorMessage
            );
        }
    }

    /**
     * Create subscription release
     *
     * @param SubscriptionInterface $subscription
     * @param Order $order
     * @throws LocalizedException
     * @return void
     */
    private function createRelease(SubscriptionInterface $subscription, Order $order): void
    {
        try {
            /** @var SubscriptionRelease $release */
            $release = $this->subscriptionReleaseInterfaceFactory->create();
            $release->setSubscriptionId($subscription->getId())
                ->setCustomerId($subscription->getCustomerId())
                ->setOrderId((int) $order->getEntityId())
                ->setStatus(SubscriptionReleaseInterface::STATUS_ACTIVE);
            $this->subscriptionReleaseResource->save($release);

            // Update subscription release dates
            $subscription->setPreviousReleaseDate($subscription->getNextReleaseDate());
            $subscription->setNextReleaseDate(date(
                'Y-m-d H:i:s',
                strtotime(sprintf('+ %d days', $subscription->getFrequency()))
            ));
            $this->subscriptionResource->save($subscription);
        } catch (AlreadyExistsException | Exception $e) {
            throw new LocalizedException(__('Could not create release: %1', $e->getMessage()));
        }
    }
}
