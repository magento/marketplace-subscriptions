<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

interface ReleaseConsumerInterface
{
    /**
     * Consume Release message and create a Release for a Subscription
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionInterface $subscription
     */
    public function execute(
        \PayPal\Subscription\Api\Data\SubscriptionInterface $subscription
    ): void;
}
