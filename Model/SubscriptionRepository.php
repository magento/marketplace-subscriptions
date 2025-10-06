<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionSearchResultInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection;
use PayPal\Subscription\Model\ResourceModel\Subscription\CollectionFactory;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * SubscriptionRepository constructor.
     *
     * @param SubscriptionInterfaceFactory $subscriptionFactory
     * @param SubscriptionResource $subscriptionResource
     * @param CollectionFactory $collectionFactory
     * @param SubscriptionSearchResultInterfaceFactory $searchResultFactory
     */
    public function __construct(
        private readonly SubscriptionInterfaceFactory $subscriptionFactory,
        private readonly SubscriptionResource $subscriptionResource,
        private readonly CollectionFactory $collectionFactory,
        private readonly SubscriptionSearchResultInterfaceFactory $searchResultFactory
    ) {
    }

    /**
     * Get by ID
     *
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
     * Get by Order ID
     *
     * @param int $orderId
     * @return SubscriptionInterface|Subscription
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): SubscriptionInterface|Subscription
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
    ): SubscriptionInterface {
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
     * Get list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResults {
        $collection = $this->collectionFactory->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();
        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Save subscription
     *
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
     * Delete subscription
     *
     * @param SubscriptionInterface $subscription
     * @return void
     * @throws Exception
     */
    public function delete(SubscriptionInterface $subscription): void
    {
        $this->subscriptionResource->delete($subscription);
    }

    /**
     * Add filters to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addFiltersToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ): void {
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
     * Add sort orders to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addSortOrdersToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ): void {
        if ($searchCriteria->getSortOrders()) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $direction = $sortOrder->getDirection() === SortOrder::SORT_ASC ? 'asc' : 'desc';
                $collection->addOrder($sortOrder->getField(), $direction);
            }
        }
    }

    /**
     * Add paging to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addPagingToCollection(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ): void {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * Build search result
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return SearchResults
     */
    private function buildSearchResult(
        SearchCriteriaInterface $searchCriteria,
        Collection $collection
    ): SearchResults {
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
