<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Email;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\Email;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use Psr\Log\LoggerInterface;

class Subscription extends Email
{
    public const TEMPLATE_NEW_SUBSCRIPTION = 'paypal_subscriptions_configuration_subscription_new';
    public const TEMPLATE_UPDATE_SUBSCRIPTION = 'paypal_subscriptions_configuration_subscription_update';
    public const TEMPLATE_RENEW_SUBSCRIPTION = 'paypal_subscriptions_configuration_subscription_renew';

    public const CONFIG_NEW_SUBSCRIPTION = 'paypal_subscriptions/configuration/subscription_new';
    public const CONFIG_UPDATE_SUBSCRIPTION = 'paypal_subscriptions/configuration/subscription_update';
    public const CONFIG_RENEW_SUBSCRIPTION = 'paypal_subscriptions/configuration/subscription_renew';


    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollection;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * Subscription constructor.
     *
     * @param TransportBuilderFactory $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollection
     * @param OrderRepository $orderRepository
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        TransportBuilderFactory $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        SubscriptionItemCollectionFactory $subscriptionItemCollection,
        OrderRepository $orderRepository,
        SubscriptionHelper $subscriptionHelper
    ) {
        parent::__construct($transportBuilder, $storeManager, $scopeConfig, $logger);
        $this->subscriptionItemCollection = $subscriptionItemCollection;
        $this->orderRepository = $orderRepository;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param CustomerInterface $customer
     * @return array
     * @throws LocalizedException
     */
    public function new(SubscriptionInterface $subscription, CustomerInterface $customer)
    {
        $subscriptionItems = [];

        /** @var Order $order */
        try {
            $order = $this->orderRepository->get($subscription->getOrderId());
        } catch (InputException | NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not find original order: %1', $e->getMessage()));
        }

        foreach ($order->getItems() as $item) {
            if ($item->getProductOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION)) {
                $subscriptionItems[] = $item;
            }
        }

        $data = [
            'store' => $order->getStore(),
            'customer_name' => sprintf('%1$s %2$s', $customer->getFirstname(), $customer->getLastname()),
            'subscription' => $subscription,
            'formattedBillingAddress' => $this->subscriptionHelper->getFormattedAddress(
                $subscription->getBillingAddress()
            ),
            'formattedShippingAddress' => $this->subscriptionHelper->getFormattedAddress(
                $subscription->getShippingAddress()
            ),
            'items' => $subscriptionItems
        ];

        $customTemplate = $this->getScopeConfig()->getValue(
            self::CONFIG_NEW_SUBSCRIPTION,
            ScopeInterface::SCOPE_STORE
        );

        return $this->sendEmail($data, $customer, $customTemplate ?? self::TEMPLATE_NEW_SUBSCRIPTION);
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param CustomerInterface $customer
     * @param array $updated
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function update(SubscriptionInterface $subscription, CustomerInterface $customer, array $updated)
    {
        /** @var Order $order */
        $order = $this->orderRepository->get($subscription->getOrderId());
        $data = [
            'store' => $order->getStore(),
            'customer_name' => sprintf('%1$s %2$s', $customer->getFirstname(), $customer->getLastname()),
            'update' => [
                'action' => $updated['action'],
                'description' => $updated['description'],
            ]
        ];

        $customTemplate = $this->getScopeConfig()->getValue(
            self::CONFIG_UPDATE_SUBSCRIPTION,
            ScopeInterface::SCOPE_STORE
        );

        return $this->sendEmail($data, $customer, $customTemplate ?? self::TEMPLATE_UPDATE_SUBSCRIPTION);
    }

    /**
     * @param $subscriptionId
     * @return array
     */
    public function getSubscriptionItems($subscriptionId): array
    {
        $items = $this->subscriptionItemCollection->create();
        $items->addFieldToFilter('subscription_id', $subscriptionId);

        return $items->getItems();
    }
}
