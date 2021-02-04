<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionHistorySearchResultInterface
 */
interface SubscriptionHistorySearchResultInterface extends SearchResultsInterface
{
    /**
     * @return SubscriptionHistoryInterface[]
     */
    public function getItems(): array;

    /**
     * @param SubscriptionHistoryInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
