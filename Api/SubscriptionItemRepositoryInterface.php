<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemSearchResultInterface;

/**
 * Interface SubscriptionItemRepositoryInterface
 */
interface SubscriptionItemRepositoryInterface
{
    /**
     * @param int $subscriptionItemId
     * @return SubscriptionItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionItemId): SubscriptionItemInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param SubscriptionItemInterface $subscriptionItem
     * @return SubscriptionItemInterface
     */
    public function save(SubscriptionItemInterface $subscriptionItem): SubscriptionItemInterface;

    /**
     * @param SubscriptionItemInterface $subscriptionItem
     * @return void
     */
    public function delete(SubscriptionItemInterface $subscriptionItem): void;
}
