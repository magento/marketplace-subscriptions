<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionItemSearchResultInterface
 */
interface SubscriptionItemSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \PayPal\Subscription\Api\Data\SubscriptionItemInterface[]
     */
    public function getItems(): array;

    /**
     * Set items
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionItemInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
