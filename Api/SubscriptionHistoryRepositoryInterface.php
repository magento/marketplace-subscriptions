<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistorySearchResultInterface;

/**
 * Interface SubscriptionHistoryRepositoryInterface
 */
interface SubscriptionHistoryRepositoryInterface
{
    /**
     * @param int $subscriptionHistoryId
     * @return SubscriptionHistoryInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionHistoryId): SubscriptionHistoryInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionHistorySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriptionHistorySearchResultInterface;

    /**
     * @param SubscriptionHistoryInterface $subscriptionHistory
     * @return SubscriptionHistoryInterface
     * @throws CouldNotSaveException
     */
    public function save(SubscriptionHistoryInterface $subscriptionHistory): SubscriptionHistoryInterface;

    /**
     * @param SubscriptionHistoryInterface $subscriptionHistory
     * @return void
     */
    public function delete(SubscriptionHistoryInterface $subscriptionHistory): void;
}
