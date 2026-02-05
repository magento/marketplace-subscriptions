<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface CreateSubscriptionOrderInterface
{
    /**
     * Convert Subscription Quote to Order
     *
     * @param CartInterface $subscriptionQuote
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function execute(
        CartInterface $subscriptionQuote
    ): OrderInterface;
}
