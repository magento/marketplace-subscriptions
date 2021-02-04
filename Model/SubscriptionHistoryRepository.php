<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionHistorySearchResultInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistorySearchResultInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionHistoryRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory as SubscriptionHistoryResource;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory\Collection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory\CollectionFactory;

class SubscriptionHistoryRepository implements SubscriptionHistoryRepositoryInterface
{
    /**
     * @var SubscriptionHistoryInterfaceFactory
     */
    private $subscriptionHistoryFactory;

    /**
     * @var SubscriptionHistoryResource
     */
    private $subscriptionHistoryResource;

    /**
     * @var CollectionFactory
     */
    private $collection;

    /**
     * @var SubscriptionHistorySearchResultInterfaceFactory
     */
    private $searchResult;

    /**
     * SubscriptionHistoryRepository constructor.
     *
     * @param SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory
     * @param SubscriptionHistoryResource $subscriptionHistoryResource
     * @param CollectionFactory $collection
     * @param SubscriptionHistorySearchResultInterfaceFactory $searchResult
     */
    public function __construct(
        SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory,
        SubscriptionHistoryResource $subscriptionHistoryResource,
        CollectionFactory $collection,
        SubscriptionHistorySearchResultInterfaceFactory $searchResult
    ) {
        $this->subscriptionHistoryFactory = $subscriptionHistoryFactory;
        $this->subscriptionHistoryResource = $subscriptionHistoryResource;
        $this->collection = $collection;
        $this->searchResult = $searchResult;
    }

    /**
     * @param int $subscriptionHistoryId
     * @return SubscriptionHistoryInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionHistoryId): SubscriptionHistoryInterface
    {
        /** @var SubscriptionHistory $subscriptionHistory */
        $subscriptionHistory = $this->subscriptionHistoryFactory->create();
        $this->subscriptionHistoryResource->load($subscriptionHistory, $subscriptionHistoryId);

        if ($subscriptionHistory->getId() === null) {
            throw new NoSuchEntityException(
                __('Unable to find Subscription History with ID "%1"', $subscriptionHistoryId)
            );
        }

        return $subscriptionHistory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionHistorySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriptionHistorySearchResultInterface
    {
        $collection = $this->collection->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * @param SubscriptionHistoryInterface $subscriptionHistory
     * @return SubscriptionHistoryInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(SubscriptionHistoryInterface $subscriptionHistory): SubscriptionHistoryInterface
    {
        $this->subscriptionHistoryResource->save($subscriptionHistory);
        return $subscriptionHistory;
    }

    /**
     * @param SubscriptionHistoryInterface $subscriptionHistory
     * @return void
     * @throws Exception
     */
    public function delete(SubscriptionHistoryInterface $subscriptionHistory): void
    {
        $this->subscriptionHistoryResource->delete($subscriptionHistory);
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
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() === SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
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
     * @return mixed
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResult->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
