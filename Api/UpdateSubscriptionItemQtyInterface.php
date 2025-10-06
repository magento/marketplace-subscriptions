<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * Interface UpdateSubscriptionItemQtyInterface to Update Subscription Item Qty
 *
 * @api
 */
interface UpdateSubscriptionItemQtyInterface
{
    /**
     * Update Subscription Item Qty by Subscription Item ID
     *
     * @param int $subscriptionItemId
     * @param int $quantity
     * @param int $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \PayPal\Subscription\Api\Data\SubscriptionItemInterface
     */
    public function execute(
        int $subscriptionItemId,
        int $quantity,
        int $customerId
    ): \PayPal\Subscription\Api\Data\SubscriptionItemInterface;
}
