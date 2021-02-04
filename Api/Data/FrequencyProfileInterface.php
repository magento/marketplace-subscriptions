<?php

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
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return FrequencyProfileInterface
     */
    public function setName(string $name): FrequencyProfileInterface;

    /**
     * @return string
     */
    public function getFrequencyOptions(): string;

    /**
     * @param string $frequencyOptions
     * @return FrequencyProfileInterface
     */
    public function setFrequencyOptions(string $frequencyOptions): FrequencyProfileInterface;

    /**
     * @return int
     */
    public function getMinReleases(): int;

    /**
     * @param int $minReleases
     * @return FrequencyProfileInterface
     */public function setMinReleases(int $minReleases): FrequencyProfileInterface;

    /**
     * @return int
     */
    public function getMaxReleases(): int;

    /**
     * @param int $maxReleases
     * @return FrequencyProfileInterface
     */public function setMaxReleases(int $maxReleases): FrequencyProfileInterface;
}
