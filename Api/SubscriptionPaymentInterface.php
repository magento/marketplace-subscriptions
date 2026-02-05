<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

interface SubscriptionPaymentInterface
{
    /**
     * Set additional payment information to quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param array $paymentData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(
        \Magento\Quote\Api\Data\CartInterface $quote,
        array $paymentData
    ): void;

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getPaymentMethodCode(): string;

    /**
     * Get additional information
     *
     * @param array $paymentData
     * @return array
     */
    public function getAdditionalInformation(array $paymentData): array;
}
