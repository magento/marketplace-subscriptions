<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Email;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\ConfigurationInterface;
use PayPal\Subscription\Model\Email;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use PayPal\Subscription\ViewModel\PaymentDetails;
use Psr\Log\LoggerInterface;

class Subscription extends Email
{
    private const TEMPLATE_NEW_SUBSCRIPTION = 'paypal_subscriptions_email_configuration_subscription_new';
    private const TEMPLATE_UPDATE_SUBSCRIPTION = 'paypal_subscriptions_email_configuration_subscription_update';
    private const TEMPLATE_SKIPPED_SUBSCRIPTION = 'paypal_subscriptions_email_configuration_release_skipped';

    private const CONFIG_NEW_SUBSCRIPTION = 'paypal_subscriptions/email_configuration/subscription_new';
    private const CONFIG_UPDATE_SUBSCRIPTION = 'paypal_subscriptions/email_configuration/subscription_update';
    private const CONFIG_SKIPPED_SUBSCRIPTION = 'paypal_subscriptions/email_configuration/release_skipped';

    private const TEMPLATE_CANCEL_SUBSCRIPTION = 'paypal_subscriptions_email_configuration_subscription_cancel';
    private const CONFIG_CANCEL_SUBSCRIPTION = 'paypal_subscriptions/email_configuration/subscription_cancel';

    private const TEMPLATE_PAUSE_SUBSCRIPTION = 'paypal_subscriptions_email_configuration_subscription_pause';
    private const CONFIG_PAUSE_SUBSCRIPTION = 'paypal_subscriptions/email_configuration/subscription_pause';

    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollectionFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var PaymentDetails
     */
    private $paymentDetails;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * Subscription constructor.
     *
     * @param TransportBuilderFactory $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     * @param OrderRepository $orderRepository
     * @param SubscriptionHelper $subscriptionHelper
     * @param ManagerInterface $eventManager
     * @param PaymentDetails $paymentDetails
     * @param ConfigurationInterface $configuration
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TransportBuilderFactory $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory,
        OrderRepository $orderRepository,
        SubscriptionHelper $subscriptionHelper,
        ManagerInterface $eventManager,
        PaymentDetails $paymentDetails,
        ConfigurationInterface $configuration,
        PaymentTokenManagementInterface $paymentTokenManagement,
        TimezoneInterface $timezone
    ) {
        parent::__construct(
            $transportBuilder,
            $storeManager,
            $scopeConfig,
            $eventManager,
            $logger,
            $configuration,
            $timezone
        );
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->paymentDetails = $paymentDetails;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Send new subscription email
     *
     * @param SubscriptionInterface $subscription
     * @param CustomerInterface $customer
     * @return array
     * @throws LocalizedException
     */
    public function new(
        SubscriptionInterface $subscription,
        CustomerInterface $customer
    ): array {
        /** @var SubscriptionItemInterface[] $subscriptionItems */
        $subscriptionItems = $this->getSubscriptionItems(
            $subscription->getId()
        );
        /** @var Order $order */
        try {
            $order = $this->orderRepository->get($subscription->getOrderId());
        } catch (InputException | NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not find original order: %1', $e->getMessage()));
        }
        $data = [
            'store' => $order->getStore(),
            'customer_name' => sprintf('%1$s %2$s', $customer->getFirstname(), $customer->getLastname()),
            'subscription' => $subscription,
            'frequency_label' => $this->getSubscribedFrequencyLabel(
                $subscriptionItems,
                $subscription->getFrequency()
            ),
            'formattedBillingAddress' => $this->subscriptionHelper->getFormattedAddress(
                $subscription->getBillingAddress()
            ),
            'formattedShippingAddress' => $this->subscriptionHelper->getFormattedAddress(
                $subscription->getShippingAddress()
            ),
            'items' => $subscriptionItems,
            'formatted_next_release' => $this->formatDate($subscription->getNextReleaseDate())
        ];

        $customTemplate = $this->getScopeConfig()->getValue(
            self::CONFIG_NEW_SUBSCRIPTION,
            ScopeInterface::SCOPE_STORE
        ) ?: self::TEMPLATE_NEW_SUBSCRIPTION;

        return $this->sendEmail(
            $data,
            $customer,
            $customTemplate
        );
    }

    /**
     * Send update subscription email
     *
     * @param SubscriptionInterface $subscription
     * @param CustomerInterface $customer
     * @param array $updated
     * @return array
     * @throws LocalizedException
     */
    public function update(
        SubscriptionInterface $subscription,
        CustomerInterface $customer,
        array $updated
    ): array {
        $paymentException = null;
        /** @var SubscriptionItemInterface[] $subscriptionItems */
        $subscriptionItems = $this->getSubscriptionItems(
            $subscription->getId()
        );
        try {
            /** @var Order|OrderInterface $order */
            $order = $this->orderRepository->get($subscription->getOrderId());
        } catch (InputException | NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not find original order: %1', $e->getMessage()));
        }
        try {
            $paymentDetails = $subscription->getPaymentData() ?: '';
            $paymentDetails = json_decode($paymentDetails, true);
            $paymentPublicHash = $paymentDetails['public_hash'] ?? null;
            /** @var PaymentTokenInterface $paymentToken */
            $paymentToken = $this->paymentTokenManagement->getByPublicHash(
                $paymentPublicHash,
                (int) $subscription->getCustomerId()
            );
            $determinedPaymentDetails = $paymentToken !== null;
        } catch (NoSuchEntityException $e) {
            $determinedPaymentDetails = false;
            $paymentException = $e;
        }
        if ($determinedPaymentDetails === false &&
            $paymentException !== null) {
            throw new LocalizedException(__(
                'Could not determine the Subscription Payment Details: %1',
                $e->getMessage()
            ));
        }
        $data = [
            'store' => $order->getStore(),
            'customer_name' => sprintf('%1$s %2$s', $customer->getFirstname(), $customer->getLastname()),
            'subscription' => $subscription,
            'frequency_label' => $this->getSubscribedFrequencyLabel(
                $subscriptionItems,
                $subscription->getFrequency()
            ),
            'items' => $subscriptionItems,
            'formattedBillingAddress' => $this->subscriptionHelper->getFormattedAddress(
                $subscription->getBillingAddress()
            ),
            'formattedShippingAddress' => $this->subscriptionHelper->getFormattedAddress(
                $subscription->getShippingAddress()
            ),
            'update' => [
                'action' => $updated['action'],
                'description' => $updated['description'],
            ],
            'maskedCC' => $this->paymentDetails->getMaskedCardNumber($subscription) ?: __('Not visible'),
            'expirationDate' => $this->paymentDetails->getCardExpiry($subscription) ?: __('Not visible'),
            'payment' => $paymentToken,
            'formatted_next_release' => $this->formatDate(
                $subscription->getNextReleaseDate()
            )
        ];
        if ($subscription->getStatus() === SubscriptionInterface::STATUS_CANCELLED) {
            $customTemplate = $this->getScopeConfig()->getValue(
                self::CONFIG_CANCEL_SUBSCRIPTION,
                ScopeInterface::SCOPE_STORE
            ) ?? self::TEMPLATE_CANCEL_SUBSCRIPTION;
        } elseif ($subscription->getStatus() === SubscriptionInterface::STATUS_PAUSED) {
            $customTemplate = $this->getScopeConfig()->getValue(
                self::CONFIG_PAUSE_SUBSCRIPTION,
                ScopeInterface::SCOPE_STORE
            ) ?? self::TEMPLATE_PAUSE_SUBSCRIPTION;
        } else {
            $customTemplate = $this->getScopeConfig()->getValue(
                self::CONFIG_UPDATE_SUBSCRIPTION,
                ScopeInterface::SCOPE_STORE
            ) ?? self::TEMPLATE_UPDATE_SUBSCRIPTION;
        }
        return $this->sendEmail(
            $data,
            $customer,
            $customTemplate
        );
    }

    /**
     * Skipped subscription email
     *
     * @param SubscriptionInterface $subscription
     * @param CustomerInterface $customer
     * @return void
     */
    public function skippedSubscription(
        SubscriptionInterface $subscription,
        CustomerInterface $customer
    ): void {
        /** @var SubscriptionItemInterface[] $subscriptionItems */
        $subscriptionItems = $this->getSubscriptionItems(
            $subscription->getId()
        );
        $data = [
            'customer_name' => sprintf(
                '%1$s %2$s',
                $customer->getFirstname(),
                $customer->getLastname()
            ),
            'subscription' => $subscription,
            'frequency_label' => $this->getSubscribedFrequencyLabel(
                $subscriptionItems,
                $subscription->getFrequency()
            ),
            'items' => $subscriptionItems,
            'formatted_next_release' => $this->formatDate(
                $subscription->getNextReleaseDate()
            )
        ];
        $customTemplate = $this->getScopeConfig()->getValue(
            self::CONFIG_SKIPPED_SUBSCRIPTION,
            ScopeInterface::SCOPE_STORE
        ) ?? self::TEMPLATE_SKIPPED_SUBSCRIPTION;
        $this->sendEmail(
            $data,
            $customer,
            $customTemplate
        );
    }

    /**
     * Get subscribed frequency label
     *
     * @param SubscriptionItemInterface[] $items
     * @param int $frequency
     * @return string
     */
    public function getSubscribedFrequencyLabel(array $items, int $frequency): string
    {
        $productId = null;
        foreach ($items as $item) {
            $productId = $item->getProductId();
            break;
        }

        return ($productId) ? $this->subscriptionHelper->getIntervalLabel(
            $productId,
            $frequency
        ) : '';
    }

    /**
     * Get subscription items
     *
     * @param int $subscriptionId
     * @return array
     */
    public function getSubscriptionItems(
        int $subscriptionId
    ): array {
        $items = $this->subscriptionItemCollectionFactory->create();
        $items->addFieldToFilter(
            'subscription_id',
            $subscriptionId
        );
        return $items->getItems();
    }
}
