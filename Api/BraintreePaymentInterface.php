<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

interface BraintreePaymentInterface
{
    /**
     * Change and save new payment method data to the subscription
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param string $nonce
     * @param string $paymentType
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     */
    public function changePaymentMethodNew(
        int $customerId,
        int $subscriptionId,
        string $nonce,
        string $paymentType
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;
}
