<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Sales\Model\Order;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Model\ReleaseConsumer as Subject;

class ReleaseConsumer
{
    /**
     * Intercept before Release is created to reset subscription failures
     * At this point subscription has been converted to quote and order has been created
     *
     * @param Subject $subject
     * @param SubscriptionInterface $subscription
     * @param Order $order
     * @return array
     */
    public function beforeCreateRelease(
        Subject $subject,
        SubscriptionInterface $subscription,
        Order $order
    ): array {
        $subscription->setFailedPayments(0);
        $subscription->setStockFailures(0);
        return [$subscription, $order];
    }
}
