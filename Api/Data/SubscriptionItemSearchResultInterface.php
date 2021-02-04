<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionItemSearchResultInterface
 */
interface SubscriptionItemSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return SubscriptionItemInterface[]
     */
    public function getItems(): array;

    /**
     * @param SubscriptionItemInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
