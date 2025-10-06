<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment;

interface PaymentMethodTypeResolverInterface
{
    public const TYPE_CARD = 'card';
    public const TYPE_PAYPAL = 'paypal';

    /**
     * Return 'type' of payment based off method code
     *
     * @param string $paymentMethodCode
     * @return string|null
     */
    public function execute(string $paymentMethodCode): ?string;
}
