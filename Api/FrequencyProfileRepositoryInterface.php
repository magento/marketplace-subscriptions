<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Api\Data\FrequencyProfileSearchResultInterface;

/**
 * Interface FrequencyProfileRepositoryInterface
 */
interface FrequencyProfileRepositoryInterface
{
    /**
     * @param int $frequencyProfileId
     * @return FrequencyProfileInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $frequencyProfileId): FrequencyProfileInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return FrequencyProfileSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FrequencyProfileSearchResultInterface;

    /**
     * @param FrequencyProfileInterface $frequencyProfile
     * @return FrequencyProfileInterface
     * @throws CouldNotSaveException
     */
    public function save(FrequencyProfileInterface $frequencyProfile): FrequencyProfileInterface;

    /**
     * @param FrequencyProfileInterface $frequencyProfile
     * @return void
     */
    public function delete(FrequencyProfileInterface $frequencyProfile): void;
}
