<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface FrequencyProfileSearchResultInterface
 */
interface FrequencyProfileSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return FrequencyProfileInterface[]
     */
    public function getItems(): array;

    /**
     * @param FrequencyProfileInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
