<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment\Braintree;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Payment;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use PayPal\Subscription\Api\SubscriptionPaymentInterface;

abstract class AbstractBraintreePayment implements SubscriptionPaymentInterface
{
    /**
     * @var BraintreeAdapter
     */
    protected BraintreeAdapter $braintreeAdapter;

    /**
     * AbstractBraintreePayment constructor.
     *
     * @param BraintreeAdapter $braintreeAdapter
     */
    public function __construct(BraintreeAdapter $braintreeAdapter)
    {
        $this->braintreeAdapter = $braintreeAdapter;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        CartInterface $quote,
        array $paymentData
    ): void {
        $paymentMethodNonce = $this->braintreeAdapter->createNonce($paymentData['gateway_token']);
        /** @var Payment $quotePayment */
        $quotePayment = $quote->getPayment();
        $quotePayment->setMethod($this->getPaymentMethodCode());
        $paymentData['customer_id'] = $quote->getCustomerId();
        $paymentData['payment_method_nonce'] = $paymentMethodNonce->paymentMethodNonce->nonce;
        $quotePayment->setAdditionalInformation($this->getAdditionalInformation($paymentData));
    }

    /**
     * Get payment method code
     *
     * @return string
     */
    abstract public function getPaymentMethodCode(): string;

    /**
     * Get payment additional information
     *
     * @param array $paymentData
     * @return array
     */
    abstract public function getAdditionalInformation(array $paymentData): array;
}
