<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * @api
 */
interface QuoteManagementInterface
{
    /**
     * Change frequency
     *
     * @param int $cartId
     * @param int $quoteItemId
     * @param int $frequency
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function changeFrequency(
        int $cartId,
        int $quoteItemId,
        int $frequency
    ): \Magento\Quote\Api\Data\CartInterface;
}
