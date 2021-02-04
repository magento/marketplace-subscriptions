<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderItemInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

interface SubscriptionItemManagementInterface
{
    /**
     * @param SubscriptionInterface $subscription
     * @param OrderItemInterface $item
     * @return mixed
     */
    public function createSubscriptionItem(SubscriptionInterface $subscription, OrderItemInterface $item);
}
