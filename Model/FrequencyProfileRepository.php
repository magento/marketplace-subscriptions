<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Api\Data\FrequencyProfileSearchResultInterface;
use PayPal\Subscription\Api\Data\FrequencyProfileSearchResultInterfaceFactory;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyProfileResource;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile\CollectionFactory;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile\Collection;

class FrequencyProfileRepository implements FrequencyProfileRepositoryInterface
{
    /**
     * @var FrequencyProfileFactory
     */
    private $frequencyProfileFactory;

    /**
     * @var FrequencyProfileResource
     */
    private $frequencyProfileResource;

    /**
     * @var ResourceModel\FrequencyProfile\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FrequencyProfileSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * FrequencyProfileRepository constructor.
     * @param FrequencyProfileFactory $frequencyProfileFactory
     * @param FrequencyProfileResource $frequencyProfileResource
     * @param CollectionFactory $collectionFactory
     * @param FrequencyProfileSearchResultInterfaceFactory $searchResultFactory
     */
    public function __construct(
        FrequencyProfileFactory $frequencyProfileFactory,
        FrequencyProfileResource $frequencyProfileResource,
        CollectionFactory $collectionFactory,
        FrequencyProfileSearchResultInterfaceFactory $searchResultFactory
    ) {
        $this->frequencyProfileFactory = $frequencyProfileFactory;
        $this->frequencyProfileResource = $frequencyProfileResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param int $frequencyProfileId
     * @return FrequencyProfileInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $frequencyProfileId): FrequencyProfileInterface
    {
        /** @var FrequencyProfile $frequencyProfile */
        $frequencyProfile = $this->frequencyProfileFactory->create();
        $this->frequencyProfileResource->load($frequencyProfile, $frequencyProfileId);

        if ($frequencyProfile->getId() === null) {
            throw new NoSuchEntityException(__('Unable to find Frequency Profile with ID "%1"', $frequencyProfileId));
        }

        return $frequencyProfile;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return FrequencyProfileSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FrequencyProfileSearchResultInterface
    {
        $collection = $this->collectionFactory->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * @param FrequencyProfileInterface $frequencyProfile
     * @return FrequencyProfileInterface
     * @throws AlreadyExistsException
     */
    public function save(FrequencyProfileInterface $frequencyProfile): FrequencyProfileInterface
    {
        $this->frequencyProfileResource->save($frequencyProfile);

        return $frequencyProfile;
    }

    /**
     * @param FrequencyProfileInterface $frequencyProfile
     * @return void
     * @throws Exception
     */
    public function delete(FrequencyProfileInterface $frequencyProfile): void
    {
        $this->frequencyProfileResource->delete($frequencyProfile);
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
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
