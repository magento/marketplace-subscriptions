<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionItemSearchResultInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemSearchResultInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionItemRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\Collection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem as SubscriptionItemResource;

class SubscriptionItemRepository implements SubscriptionItemRepositoryInterface
{
    /**
     * @var SubscriptionItemInterfaceFactory
     */
    private $subscriptionItem;

    /**
     * @var ResourceModel\SubscriptionItem
     */
    private $subscriptionItemResource;

    /**
     * @var CollectionFactory
     */
    private $collection;

    /**
     * @var SubscriptionItemSearchResultInterfaceFactory
     */
    private $searchResult;

    /**
     * SubscriptionItemRepository constructor.
     *
     * @param SubscriptionItemInterfaceFactory $subscriptionItem
     * @param ResourceModel\SubscriptionItem $subscriptionItemResource
     * @param CollectionFactory $collection
     * @param SubscriptionItemSearchResultInterfaceFactory $searchResult
     */
    public function __construct(
        SubscriptionItemInterfaceFactory $subscriptionItem,
        SubscriptionItemResource $subscriptionItemResource,
        CollectionFactory $collection,
        SubscriptionItemSearchResultInterfaceFactory $searchResult
    ) {
        $this->subscriptionItem = $subscriptionItem;
        $this->subscriptionItemResource = $subscriptionItemResource;
        $this->collection = $collection;
        $this->searchResult = $searchResult;
    }

    /**
     * Get by ID
     *
     * @param int $subscriptionItemId
     * @return SubscriptionItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $subscriptionItemId): SubscriptionItemInterface
    {
        /** @var SubscriptionItem $subscriptionItem */
        $subscriptionItem = $this->subscriptionItem->create();
        $this->subscriptionItemResource->load(
            $subscriptionItem,
            $subscriptionItemId
        );
        if ($subscriptionItem->getId() === null) {
            throw new NoSuchEntityException(__('Unable to find Subscription Item with ID "%1"', $subscriptionItemId));
        }
        return $subscriptionItem;
    }

    /**
     * Get list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionItemSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriptionItemSearchResultInterface
    {
        $collection = $this->collection->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * Save subscription item
     *
     * @param SubscriptionItemInterface $subscriptionItem
     * @return SubscriptionItemInterface
     * @throws AlreadyExistsException
     */
    public function save(SubscriptionItemInterface $subscriptionItem): SubscriptionItemInterface
    {
        $this->subscriptionItemResource->save($subscriptionItem);

        return $subscriptionItem;
    }

    /**
     * Delete subscription item
     *
     * @param SubscriptionItemInterface $subscriptionItem
     * @return void
     * @throws Exception
     */
    public function delete(SubscriptionItemInterface $subscriptionItem): void
    {
        $this->subscriptionItemResource->delete($subscriptionItem);
    }

    /**
     * Add filters to collection
     *
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
     * Add sort orders to collection
     *
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
     * Add paging to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * Build search result
     *
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
