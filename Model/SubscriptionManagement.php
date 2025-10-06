<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use DateMalformedStringException;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionItemManagementInterface;
use PayPal\Subscription\Api\SubscriptionManagementInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use Magento\Framework\Message\ManagerInterface;

class SubscriptionManagement implements SubscriptionManagementInterface
{
    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscription;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionItemManagementInterface
     */
    private $subscriptionItemManagement;

    /**
     * @var SubscriptionHelper
     */
    private $helper;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * SubscriptionManagement constructor.
     *
     * @param SubscriptionInterfaceFactory $subscription
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SubscriptionItemManagementInterface $subscriptionItemManagement
     * @param SubscriptionHelper $helper
     * @param AddressRepository $addressRepository
     * @param ManagerInterface $messageManager
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param SerializerInterface $serializer
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EventManagerInterface $eventManager
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        SubscriptionInterfaceFactory $subscription,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionItemManagementInterface $subscriptionItemManagement,
        SubscriptionHelper $helper,
        AddressRepository $addressRepository,
        ManagerInterface $messageManager,
        PaymentTokenManagementInterface $paymentTokenManagement,
        SerializerInterface $serializer,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EventManagerInterface $eventManager,
        ConfigurationInterface $configuration
    ) {
        $this->subscription = $subscription;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionItemManagement = $subscriptionItemManagement;
        $this->helper = $helper;
        $this->addressRepository = $addressRepository;
        $this->messageManager = $messageManager;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->serializer = $serializer;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->eventManager = $eventManager;
        $this->configuration = $configuration;
    }

    /**
     * Create subscription with item
     *
     * @param OrderInterface $order
     * @param OrderItemInterface $item
     * @param int $frequency
     * @param int|null $frequencyProfileId
     * @return SubscriptionInterface
     * @throws LocalizedException
     */
    public function createSubscriptionWithItem(
        OrderInterface $order,
        OrderItemInterface $item,
        int $frequency,
        ?int $frequencyProfileId = null
    ): SubscriptionInterface {
        $subscription = $this->createSubscription(
            $order,
            $frequency,
            $frequencyProfileId
        );
        $this->subscriptionItemManagement->createSubscriptionItem($subscription, $item);
        $this->eventManager->dispatch(
            'subscription_create_with_item_after',
            ['subscription' => $subscription]
        );
        return $subscription;
    }

    /**
     * Create subscription
     *
     * @param OrderInterface $order
     * @param int $frequency
     * @param int|null $frequencyProfileId
     * @return SubscriptionInterface
     * @throws LocalizedException
     */
    public function createSubscription(
        OrderInterface $order,
        int $frequency,
        ?int $frequencyProfileId = null
    ): SubscriptionInterface {
        if (!$order->getCustomerId()) {
            throw new LocalizedException(__('Customer ID missing.'));
        }

        $payment = $order->getPayment();
        $paymentMethod = $this->paymentTokenManagement->getByPaymentId($payment->getEntityId());

        try {
            /** @var Subscription $subscription */
            $subscription = $this->subscription->create();
            $subscription->setCustomerId((int) $order->getCustomerId());
            $subscription->setOrderId((int) $order->getId());
            $subscription->setStatus(SubscriptionInterface::STATUS_ACTIVE);
            $subscription->setNextReleaseDate(
                date(
                    'Y-m-d H:i:s',
                    strtotime(sprintf('+ %d days', $frequency))
                )
            );
            $subscription->setFrequencyProfileId($frequencyProfileId ? (int) $frequencyProfileId : null);
            $subscription->setFrequency($frequency);
            $subscription->setBillingAddress($this->helper->getSerialisedAddress($order->getBillingAddress()));
            $subscription->setShippingAddress($this->helper->getSerialisedAddress($order->getShippingAddress()));
            $subscription->setShippingPrice(((float) $order->getShippingAmount()) ?? 0.00);
            $shippingMethod = $this->configuration->getReleaseShippingMethod() ?: $order->getShippingMethod();
            $subscription->setShippingMethod($shippingMethod);
            $subscription->setPaymentMethod($payment->getMethod());
            if ($paymentMethod) {
                $subscription->setPaymentData(
                    $this->serializer->serialize(
                        [
                            'gateway_token' => $paymentMethod->getGatewayToken(),
                            'public_hash' => $paymentMethod->getPublicHash()
                        ]
                    )
                );
            }
            $this->subscriptionRepository->save($subscription);
        } catch (AlreadyExistsException $e) {
            throw new LocalizedException(__(
                'Could not save subscription. %1',
                $e->getMessage()
            ));
        }

        return $subscription;
    }

    /**
     * Change frequency
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $frequency
     * @return SubscriptionInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException|DateMalformedStringException
     */
    public function changeFrequency(
        int $customerId,
        int $subscriptionId,
        int $frequency
    ): SubscriptionInterface {
        try {
            $subscription = $this->subscriptionRepository->getCustomerSubscription($customerId, $subscriptionId);
            $oldFrequency = $subscription->getFrequency();
            $subscription->setFrequency($frequency);
            $difference = $frequency - $oldFrequency;
            $nextRelease = $subscription->getNextReleaseDate();
            $date = new \DateTime($nextRelease);
            $date->modify($difference . "days");
            $today = new \DateTime();
            if ($date < $today) {
                $date = $today->modify('1 day');
            }
            $subscription->setNextReleaseDate(
                $date->format("Y-m-d H:i:s")
            );
            $this->subscriptionRepository->save($subscription);
            $this->messageManager->addSuccessMessage(__('Your subscription frequency has been updated.'));

            $subscription->addHistory(
                SubscriptionHistoryInterface::CHANGE_FREQUENCY_ACTION,
                'customer',
                sprintf(
                    'The frequency interval was updated from %d to %d',
                    $oldFrequency,
                    $frequency
                )
            );

            return $subscription;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Your subscription frequency could not be updated.'));
            throw new LocalizedException(__('Your subscription frequency could not be updated.'));
        }
    }

    /**
     * Change status
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $status
     * @return SubscriptionInterface
     * @throws LocalizedException
     */
    public function changeStatus(
        int $customerId,
        int $subscriptionId,
        int $status
    ): SubscriptionInterface {
        try {
            $subscription = $this->subscriptionRepository->getCustomerSubscription(
                $customerId,
                $subscriptionId
            );
            $oldStatus = $subscription->getStatus();
            $subscription->setStatus($status);
            $this->subscriptionRepository->save($subscription);
            $subscription->addHistory(
                SubscriptionHistoryInterface::CHANGE_STATUS_ACTION,
                'customer',
                sprintf('The status was updated from %d to %d', $oldStatus, $status)
            );
            return $subscription;
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not update status.'));
        }
    }

    /**
     * Change existing address
     *
     * @param int $customerId
     * @param string $addressType
     * @param int $subscriptionId
     * @param int $addressId
     * @return AddressInterface
     * @throws LocalizedException
     */
    public function changeAddressExisting(
        int $customerId,
        string $addressType,
        int $subscriptionId,
        int $addressId
    ): AddressInterface {
        try {
            $subscription = $this->subscriptionRepository->getCustomerSubscription($customerId, $subscriptionId);
            $address = $this->addressRepository->getById($addressId);

            if ((int) $address->getCustomerId() !== $customerId) {
                throw new LocalizedException(__('Customer address mismatch.'));
            }

            switch ($addressType) {
                case Address::TYPE_BILLING:
                    $subscription->setBillingAddress($this->helper->getSerialisedAddress($address));
                    break;
                case Address::TYPE_SHIPPING:
                    $subscription->setShippingAddress($this->helper->getSerialisedAddress($address));
                    break;
            }

            $this->subscriptionRepository->save($subscription);

            $subscription->addHistory(
                SubscriptionHistoryInterface::CHANGE_ADDRESS_ACTION,
                'customer',
                sprintf('The %s address was updated', $addressType)
            );

            $this->messageManager->addSuccessMessage(__('Your address has been updated.'));

            return $address;
        } catch (NoSuchEntityException | AlreadyExistsException | LocalizedException $e) {

            $this->messageManager->addErrorMessage(__('Your address could not be updated.'));
            throw new LocalizedException(__('Could not update %1 address.', $addressType));
        }
    }

    /**
     * Change new address
     *
     * @param int $customerId
     * @param string $addressType
     * @param int $subscriptionId
     * @param AddressInterface $address
     * @return AddressInterface
     * @throws LocalizedException
     */
    public function changeAddressNew(
        int $customerId,
        string $addressType,
        int $subscriptionId,
        AddressInterface $address
    ): AddressInterface {
        try {
            $subscription = $this->subscriptionRepository->getCustomerSubscription(
                $customerId,
                $subscriptionId
            );

            $address->setCustomerId($customerId);
            $newAddress = $this->addressRepository->save($address);

            switch ($addressType) {
                case Address::TYPE_BILLING:
                    $subscription->setBillingAddress(
                        $this->helper->getSerialisedAddress(
                            $newAddress
                        )
                    );
                    break;
                case Address::TYPE_SHIPPING:
                    $subscription->setShippingAddress(
                        $this->helper->getSerialisedAddress(
                            $newAddress
                        )
                    );
                    break;
            }
            $this->subscriptionRepository->save($subscription);

            $subscription->addHistory(
                SubscriptionHistoryInterface::ADD_ADDRESS_ACTION,
                'customer',
                sprintf('A new %s address was added', $addressType)
            );

            $this->messageManager->addSuccessMessage(__('Your new address has been added.'));

            return $newAddress;
        } catch (NoSuchEntityException | AlreadyExistsException | LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Your new address could not be added.'));
            throw new LocalizedException(__('Could not add new %1 address.', $addressType));
        }
    }

    /**
     * Change payment method
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param string $paymentPublicHash
     * @return SubscriptionInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function changePaymentMethod(
        int $customerId,
        int $subscriptionId,
        string $paymentPublicHash
    ): SubscriptionInterface {
        try {
            $subscription = $this->subscriptionRepository->getCustomerSubscription(
                $customerId,
                $subscriptionId
            );
            $paymentMethod = $this->paymentTokenManagement->getByPublicHash(
                $paymentPublicHash,
                $customerId
            );
            $subscription->setPaymentMethod(
                $paymentMethod->getPaymentMethodCode()
            );
            $subscription->setPaymentData(
                $this->serializer->serialize(
                    [
                        'gateway_token' => $paymentMethod->getGatewayToken(),
                        'public_hash' => $paymentMethod->getPublicHash()
                    ]
                )
            );
            $this->subscriptionRepository->save($subscription);

            $subscription->addHistory(
                SubscriptionHistoryInterface::CHANGE_PAYMENT_METHOD,
                'customer',
                sprintf(
                    'The payment method was updated to %s',
                    ($paymentMethod->getPaymentMethodCode() === 'braintree' ? 'Credit Card' : 'PayPal')
                )
            )->save();
            $this->messageManager->addSuccessMessage(__('Your payment method was updated.'));
            return $subscription;
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not update payment method.'));
        }
    }

    /**
     * Collect releases
     *
     * @param string $from
     * @param string $to
     * @param bool|null $emailReminder
     * @return SubscriptionInterface[]
     */
    public function collectReleases(
        string $from,
        string $to,
        ?bool $emailReminder = null
    ): array {
        $filterFrom = $this->filterBuilder
            ->setField(SubscriptionInterface::NEXT_RELEASE_DATE)
            ->setConditionType('gteq')
            ->setValue($from)
            ->create();
        $filterTo = $this->filterBuilder
            ->setField(SubscriptionInterface::NEXT_RELEASE_DATE)
            ->setConditionType('lteq')
            ->setValue($to)
            ->create();
        $filterStatus = $this->filterBuilder
            ->setField(SubscriptionInterface::STATUS)
            ->setConditionType('eq')
            ->setValue(SubscriptionInterface::STATUS_ACTIVE)
            ->create();
        $filterGroupFrom = $this->filterGroupBuilder
            ->addFilter($filterFrom)
            ->create();
        $filterGroupTo = $this->filterGroupBuilder
            ->addFilter($filterTo)
            ->create();
        $filterGroupStatus = $this->filterGroupBuilder
            ->addFilter($filterStatus)
            ->create();
        $filterGroups = [
            $filterGroupFrom,
            $filterGroupTo,
            $filterGroupStatus
        ];
        if ($emailReminder !== null && is_bool($emailReminder)) {
            $filterEmailReminder = $this->filterBuilder
                ->setField(SubscriptionInterface::REMINDER_EMAIL_SENT)
                ->setConditionType('eq')
                ->setValue((int) $emailReminder)
                ->create();
            $filterEmailReminder = $this->filterGroupBuilder
                ->addFilter($filterEmailReminder)
                ->create();
            $filterGroups[] = $filterEmailReminder;
        }
        $this->searchCriteriaBuilder->setFilterGroups($filterGroups);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->subscriptionRepository->getList(
            $searchCriteria
        )->getItems();
    }
}
