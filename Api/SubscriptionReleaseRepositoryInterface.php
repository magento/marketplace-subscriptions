<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

interface SubscriptionReleaseRepositoryInterface
{
    /**
     * Get subscription release by Id
     *
     * @param int $subscriptionReleaseId
     * @return \PayPal\Subscription\Api\Data\SubscriptionReleaseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $subscriptionReleaseId);

    /**
     * Get subscription release list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PayPal\Subscription\Api\Data\SubscriptionReleaseSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Save subscription release
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionReleaseInterface $subscription
     * @return \PayPal\Subscription\Api\Data\SubscriptionReleaseInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\PayPal\Subscription\Api\Data\SubscriptionReleaseInterface $subscription);

    /**
     * Delete subscription release
     *
     * @param \PayPal\Subscription\Api\Data\SubscriptionReleaseInterface $subscription
     * @return void
     */
    public function delete(\PayPal\Subscription\Api\Data\SubscriptionReleaseInterface $subscription): void;
}
