<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

interface CreateSubscriptionQuoteInterface
{
    /**
     * Create Quote for Subscription
     *
     * @param SubscriptionInterface $subscription
     * @return CartInterface
     * @throws LocalizedException
     */
    public function execute(
        SubscriptionInterface $subscription
    ): CartInterface;
}
