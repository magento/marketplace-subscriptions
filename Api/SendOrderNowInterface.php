<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * Interface SendOrderNowInterface to Send upcoming Subscription Order now
 * @api
 */
interface SendOrderNowInterface
{
    /**
     * Send upcoming Subscription Order now by Subscription ID
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        int $subscriptionId,
        int $customerId
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;
}
