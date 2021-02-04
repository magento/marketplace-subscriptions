<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment\Braintree;

class PayPal extends AbstractBraintreePayment
{

    /**
     * @return string
     */
    public function getPaymentMethodCode(): string
    {
        return 'braintree_paypal';
    }

    /**
     * @param $paymentData
     * @return array
     */
    public function getAdditionalInformation($paymentData): array
    {
        return [
            'is_active_payment_token_enabler' => true,
            'payment_method_nonce' => $paymentData['payment_method_nonce'],
            'customer_id' => $paymentData['customer_id'],
            'public_hash' => $paymentData['public_hash']
        ];
    }
}
