<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Quote\Api\Data\CartInterface;

interface SubscriptionPaymentInterface
{
    /**
     * @param CartInterface $quote
     * @param array $paymentData
     * @return void
     */
    public function execute(CartInterface $quote, array $paymentData): void;

    /**
     * @return string
     */
    public function getPaymentMethodCode(): string;

    /**
     * @param $paymentData
     * @return array
     */
    public function getAdditionalInformation($paymentData): array;
}
