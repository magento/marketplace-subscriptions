<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionReleaseSearchResultInterface
 */
interface SubscriptionReleaseSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get release items
     *
     * @return \PayPal\Subscription\Api\Data\SubscriptionReleaseInterface[]
     */
    public function getItems(): array;

    /**
     * Set release items
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionReleaseInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
