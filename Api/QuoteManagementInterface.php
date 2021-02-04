<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * @api
 */
interface QuoteManagementInterface
{

    /**
     * @param int $cartId
     * @param int $quoteItemId
     * @param int $frequency
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function changeFrequency(
        $cartId,
        int $quoteItemId,
        int $frequency
    ): \Magento\Quote\Api\Data\CartInterface;
}
