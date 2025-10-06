<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface FrequencyProfileSearchResultInterface
 */
interface FrequencyProfileSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \PayPal\Subscription\Api\Data\FrequencyProfileInterface[]
     */
    public function getItems(): array;

    /**
     * Set items
     *
     * @param \PayPal\Subscription\Api\Data\FrequencyProfileInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
