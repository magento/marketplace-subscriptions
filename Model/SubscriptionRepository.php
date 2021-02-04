<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionSearchResultInterface;
use PayPal\Subscription\Api\Data\SubscriptionSearchResultInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection;
use PayPal\Subscription\Model\ResourceModel\Subscription\CollectionFactory;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var ResourceModel\Subscription\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SubscriptionSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * SubscriptionRepository constructor.
     *
     * @param SubscriptionInterfaceFactory $subscriptionFactory
     * @param SubscriptionResource $subscriptionResource
     * @param ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param SubscriptionSearchResultInterfaceFactory $searchResultFactory
     * @param State $state
     */
    public function __construct(
        SubscriptionInterfaceFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        CollectionFactory $collectionFactory,
        SubscriptionSearchResultInterfaceFactory $searchResultFactory,
        State $state
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->state = $state;
    }

    /**
     * @param int $subscriptionId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionId): SubscriptionInterface
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, $subscriptionId);

        if ($subscription->getId() === null) {
            throw new NoSuchEntityException(__('Unable to find Subscription with ID "%1"', $subscriptionId));
        }

        return $subscription;
    }

    /**
     * @param int $orderId
     * @return bool|SubscriptionInterface|Subscription
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SubscriptionInterface::ORDER_ID, $orderId);
        /** @var Subscription $data */
        $data = $collection->getFirstItem();

        if (!$data->getData()) {
            throw new NoSuchEntityException(__('Unable to find Subscription with Order ID "%1"', $orderId));
        }

        return $data;
    }

    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getCustomerSubscription(int $customerId, int $subscriptionId): SubscriptionInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('id', $subscriptionId);
        /** @var Subscription $data */
        $data = $collection->getFirstItem();

        if (!$data->getData()) {
            throw new NoSuchEntityException(__('Unable to find Subscription with ID "%1"', $subscriptionId));
        }

        return $data;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function save(SubscriptionInterface $subscription): SubscriptionInterface
    {
        $this->subscriptionResource->save($subscription);
        return $subscription;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return void
     * @throws Exception
     */
    public function delete(SubscriptionInterface $subscription): void
    {
        $this->subscriptionResource->delete($subscription);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        if ($searchCriteria->getSortOrders()) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $direction = $sortOrder->getDirection() === SortOrder::SORT_ASC ? 'asc' : 'desc';
                $collection->addOrder($sortOrder->getField(), $direction);
            }
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return SubscriptionSearchResultInterface
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
