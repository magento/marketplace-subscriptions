<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyResource;

class FrequencyProfile extends AbstractModel implements FrequencyProfileInterface
{
    /**
     * Initialise the Resource Model.
     */
    public function _construct()
    {
        $this->_init(FrequencyResource::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return FrequencyProfileInterface
     */
    public function setName(string $name): FrequencyProfileInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getFrequencyOptions(): string
    {
        return $this->getData(self::FREQ_OPTIONS);
    }

    /**
     * @param string $frequencyOptions
     * @return FrequencyProfileInterface
     */
    public function setFrequencyOptions(string $frequencyOptions): FrequencyProfileInterface
    {
        return $this->setData(self::FREQ_OPTIONS, $frequencyOptions);
    }

    /**
     * @return int
     */
    public function getMinReleases(): int
    {
        return $this->getData(self::MIN_RELEASES);
    }

    /**
     * @param int $minReleases
     * @return FrequencyProfileInterface
     */
    public function setMinReleases(int $minReleases): FrequencyProfileInterface
    {
        return $this->setData(self::MIN_RELEASES, $minReleases);
    }

    /**
     * @return int
     */
    public function getMaxReleases(): int
    {
        return $this->getData(self::MAX_RELEASES);
    }

    /**
     * @param int $maxReleases
     * @return FrequencyProfileInterface
     */
    public function setMaxReleases(int $maxReleases): FrequencyProfileInterface
    {
        return $this->setData(self::MAX_RELEASES, $maxReleases);
    }
}
