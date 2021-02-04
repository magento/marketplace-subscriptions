<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionSearchResultInterface
 */
interface SubscriptionSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return SubscriptionInterface[]
     */
    public function getItems(): array;

    /**
     * @param SubscriptionInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
