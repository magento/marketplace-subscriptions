<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionSearchResultInterface
 */
interface SubscriptionSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get subscription items
     *
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface[]
     */
    public function getItems(): array;

    /**
     * Set subscription items
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionInterface[] $items
     * @return void
     */
    public function setItems(array $items): void;
}
