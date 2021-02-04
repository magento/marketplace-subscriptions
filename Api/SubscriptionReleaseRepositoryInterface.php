<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionReleaseInterface;
use PayPal\Subscription\Api\Data\SubscriptionReleaseSearchResultInterface;

interface SubscriptionReleaseRepositoryInterface
{
    /**
     * @param int $subscriptionReleaseId
     * @return SubscriptionReleaseInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionReleaseId): SubscriptionReleaseInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionReleaseSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriptionReleaseSearchResultInterface;

    /**
     * @param SubscriptionReleaseInterface $subscription
     * @return SubscriptionReleaseInterface
     * @throws CouldNotSaveException
     */
    public function save(SubscriptionReleaseInterface $subscription): SubscriptionReleaseInterface;

    /**
     * @param SubscriptionReleaseInterface $subscription
     * @return void
     */
    public function delete(SubscriptionReleaseInterface $subscription): void;
}
