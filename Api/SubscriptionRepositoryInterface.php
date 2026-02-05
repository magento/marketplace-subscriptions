<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

/**
 * Interface SubscriptionRepositoryInterface
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Get subscription by ID
     *
     * @param int $subscriptionId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionId): SubscriptionInterface;

    /**
     * Get subscription by Order ID
     *
     * @param int $orderId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): SubscriptionInterface;

    /**
     * Get customer subscription
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getCustomerSubscription(
        int $customerId,
        int $subscriptionId
    ): SubscriptionInterface;

    /**
     * Get subscription list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     * @todo change this to be custom search interface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResults;

    /**
     * Save subscription
     *
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInterface
     * @throws AlreadyExistsException
     */
    public function save(SubscriptionInterface $subscription): SubscriptionInterface;

    /**
     * Delete subscription
     *
     * @param SubscriptionInterface $subscription
     * @return void
     */
    public function delete(SubscriptionInterface $subscription): void;
}
