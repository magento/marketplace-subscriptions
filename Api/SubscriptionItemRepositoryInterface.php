<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * Interface SubscriptionItemRepositoryInterface
 */
interface SubscriptionItemRepositoryInterface
{
    /**
     * Get subscription item by Id
     *
     * @param int $subscriptionItemId
     * @return \PayPal\Subscription\Api\Data\SubscriptionItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $subscriptionItemId);
    /**
     * Get subscription item list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PayPal\Subscription\Api\Data\SubscriptionItemSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Save subscription item
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionItemInterface $subscriptionItem
     * @return \PayPal\Subscription\Api\Data\SubscriptionItemInterface
     */
    public function save(\PayPal\Subscription\Api\Data\SubscriptionItemInterface $subscriptionItem);

    /**
     * Delete subscription item
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionItemInterface $subscriptionItem
     * @return void
     */
    public function delete(\PayPal\Subscription\Api\Data\SubscriptionItemInterface $subscriptionItem);
}
