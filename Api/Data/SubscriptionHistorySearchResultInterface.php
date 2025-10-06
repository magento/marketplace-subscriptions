<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionHistorySearchResultInterface
 */
interface SubscriptionHistorySearchResultInterface extends SearchResultsInterface
{
    /**
     * Get history items
     *
     * @return \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface[]
     */
    public function getItems(): array;

    /**
     * Set history items
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
