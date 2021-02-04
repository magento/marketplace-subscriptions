<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment\Braintree;

use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Payment;
use PayPal\Subscription\Api\SubscriptionPaymentInterface;

abstract class AbstractBraintreePayment implements SubscriptionPaymentInterface
{
    /**
     * @var BraintreeAdapter
     */
    protected $braintreeAdapter;

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
     * @param CartInterface $quote
     * @param array $paymentData
     * @return void
     * @throws LocalizedException
     */
    public function execute(CartInterface $quote, array $paymentData): void
    {
        $paymentMethodNonce = $this->braintreeAdapter->createNonce($paymentData['gateway_token']);

        /** @var Payment $quotePayment */
        $quotePayment = $quote->getPayment();
        $quotePayment->setMethod($this->getPaymentMethodCode());

        $paymentData['customer_id'] = $quote->getCustomerId();
        $paymentData['payment_method_nonce'] = $paymentMethodNonce->paymentMethodNonce->nonce;
        $quotePayment->setAdditionalInformation($this->getAdditionalInformation($paymentData));
    }

    abstract public function getPaymentMethodCode(): string;

    /**
     * @param $paymentData
     * @return array
     */
    abstract public function getAdditionalInformation($paymentData): array;
}
