<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
     * Get frequency profile by ID
     *
     * @param int $frequencyProfileId
     * @return FrequencyProfileInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $frequencyProfileId): FrequencyProfileInterface;

    /**
     * Get list of frequency profile
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return FrequencyProfileSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FrequencyProfileSearchResultInterface;

    /**
     * Save frequency profile
     *
     * @param FrequencyProfileInterface $frequencyProfile
     * @return FrequencyProfileInterface
     * @throws CouldNotSaveException
     */
    public function save(FrequencyProfileInterface $frequencyProfile): FrequencyProfileInterface;

    /**
     * Delete frequency profile
     *
     * @param FrequencyProfileInterface $frequencyProfile
     * @return void
     */
    public function delete(FrequencyProfileInterface $frequencyProfile): void;
}
