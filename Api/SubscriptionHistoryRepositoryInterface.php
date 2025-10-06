<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * Interface SubscriptionHistoryRepositoryInterface
 */
interface SubscriptionHistoryRepositoryInterface
{
    /**
     * Get subscription history by Id
     *
     * @param int $subscriptionHistoryId
     * @return \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $subscriptionHistoryId);

    /**
     * Get subscription history list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PayPal\Subscription\Api\Data\SubscriptionHistorySearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Save subscription history
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface $subscriptionHistory
     * @return \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\PayPal\Subscription\Api\Data\SubscriptionHistoryInterface $subscriptionHistory);

    /**
     * Delete subscription history
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface $subscriptionHistory
     * @return void
     */
    public function delete(\PayPal\Subscription\Api\Data\SubscriptionHistoryInterface $subscriptionHistory);
}
