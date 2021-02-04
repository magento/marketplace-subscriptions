<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Quote\Api\Data\CartInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

interface ReleaseConsumerInterface
{
    /**
     * @param SubscriptionInterface $subscription
     * @return mixed
     */
    public function createQuote(SubscriptionInterface $subscription);

    /**
     * @param CartInterface $quote
     * @return mixed
     */
    public function createOrder(CartInterface $quote);
}
