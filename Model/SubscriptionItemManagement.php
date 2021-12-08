<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderItemInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionItemManagementInterface;
use PayPal\Subscription\Api\SubscriptionItemRepositoryInterface;

class SubscriptionItemManagement implements SubscriptionItemManagementInterface
{
    /**
     * @var SubscriptionItemInterfaceFactory
     */
    private $subscriptionItem;

    /**
     * @var SubscriptionItemRepositoryInterface
     */
    private $subscriptionItemRepository;

    /**
     * SubscriptionItemManagement constructor.
     *
     * @param SubscriptionItemInterfaceFactory $subscriptionItem
     * @param SubscriptionItemRepositoryInterface $subscriptionItemRepository
     */
    public function __construct(
        SubscriptionItemInterfaceFactory $subscriptionItem,
        SubscriptionItemRepositoryInterface $subscriptionItemRepository
    ) {
        $this->subscriptionItem = $subscriptionItem;
        $this->subscriptionItemRepository = $subscriptionItemRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function createSubscriptionItem(SubscriptionInterface $subscription, OrderItemInterface $item)
    {
        /** @var SubscriptionItem $subscriptionItem */
        $subscriptionItem = $this->subscriptionItem->create();
        $subscriptionItem->setSubscriptionId((int) $subscription->getId());
        $subscriptionItem->setSku($item->getSku());
        $subscriptionItem->setPrice((float) $item->getPrice());
        $subscriptionItem->setQty((int) $item->getQtyOrdered());
        $subscriptionItem->setProductId((int) $item->getProductId());
        $subscriptionItem->setOrderId((int) $item->getOrderId());
        $subscriptionItem->setOrderItemId((int) $item->getItemId());
        $this->subscriptionItemRepository->save($subscriptionItem);

        return $subscriptionItem;
    }
}
