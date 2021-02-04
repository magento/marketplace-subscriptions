<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionReleaseSearchResultInterface
 */
interface SubscriptionReleaseSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return SubscriptionReleaseInterface[]
     */
    public function getItems(): array;

    /**
     * @param SubscriptionReleaseInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
