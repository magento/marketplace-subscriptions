<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionSearchResultInterface;

/**
 * Interface SubscriptionRepositoryInterface
 */
interface SubscriptionRepositoryInterface
{
    /**
     * @param int $subscriptionId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionId): SubscriptionInterface;

    /**
     * @param int $orderId
     * @return SubscriptionInterface|bool
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId);

    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getCustomerSubscription(int $customerId, int $subscriptionId): SubscriptionInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     * @todo change this to be custom search interface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInterface
     * @throws AlreadyExistsException
     */
    public function save(SubscriptionInterface $subscription): SubscriptionInterface;

    /**
     * @param SubscriptionInterface $subscription
     * @return void
     */
    public function delete(SubscriptionInterface $subscription): void;
}
