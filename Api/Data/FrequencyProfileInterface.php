<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * Interface FrequencyProfileInterface
 */
interface FrequencyProfileInterface
{
    public const PROFILE_ID = 'id';
    public const NAME = 'name';
    public const FREQ_OPTIONS = 'frequency_options';
    public const MIN_RELEASES = 'min_releases';
    public const MAX_RELEASES = 'max_releases';

    /**
     * Get frequency name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set frequency name
     *
     * @param string $name
     * @return FrequencyProfileInterface
     */
    public function setName(string $name): FrequencyProfileInterface;

    /**
     * Get frequency options
     *
     * @return string
     */
    public function getFrequencyOptions(): string;

    /**
     * Set frequency options
     *
     * @param string $frequencyOptions
     * @return FrequencyProfileInterface
     */
    public function setFrequencyOptions(string $frequencyOptions): FrequencyProfileInterface;

    /**
     * Get minimum releases
     *
     * @return int
     */
    public function getMinReleases(): int;

    /**
     * Set minimum releases
     *
     * @param int $minReleases
     * @return FrequencyProfileInterface
     */public function setMinReleases(int $minReleases): FrequencyProfileInterface;

    /**
     * Get maximum releases
     *
     * @return int
     */
    public function getMaxReleases(): int;

    /**
     * Set maximum releases
     *
     * @param int $maxReleases
     * @return FrequencyProfileInterface
     */public function setMaxReleases(int $maxReleases): FrequencyProfileInterface;
}
