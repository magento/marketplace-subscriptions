<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Sales\Api\Data\OrderItemInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

interface SubscriptionItemManagementInterface
{
    /**
     * Create subscription item
     *
     * @param SubscriptionInterface $subscription
     * @param OrderItemInterface $item
     * @return mixed
     */
    public function createSubscriptionItem(
        SubscriptionInterface $subscription,
        OrderItemInterface $item
    );
}
