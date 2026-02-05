<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment;

use Magento\Framework\Exception\InputException;
use PayPal\Subscription\Api\SubscriptionPaymentInterface;

interface PaymentMethodPoolInterface
{
    /**
     * Return all available Subscription Payment methods
     *
     * @return SubscriptionPaymentInterface[]
     */
    public function getAvailablePayments(): array;

    /**
     * Get Subscription Payment Object by method string
     *
     * @param string $paymentMethod
     * @return SubscriptionPaymentInterface
     * @throws InputException
     */
    public function getByPaymentMethod(
        string $paymentMethod
    ): SubscriptionPaymentInterface;
}
