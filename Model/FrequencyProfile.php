<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyResource;

class FrequencyProfile extends AbstractModel implements FrequencyProfileInterface
{
    /**
     * Initialise the Resource Model.
     *
     * @return void
     * @throws LocalizedException
     */
    public function _construct(): void
    {
        $this->_init(FrequencyResource::class);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return FrequencyProfileInterface
     */
    public function setName(string $name): FrequencyProfileInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get frequency options
     *
     * @return string
     */
    public function getFrequencyOptions(): string
    {
        return $this->getData(self::FREQ_OPTIONS);
    }

    /**
     * Set frequency options
     *
     * @param string $frequencyOptions
     * @return FrequencyProfileInterface
     */
    public function setFrequencyOptions(string $frequencyOptions): FrequencyProfileInterface
    {
        return $this->setData(self::FREQ_OPTIONS, $frequencyOptions);
    }

    /**
     * Get minimum releases
     *
     * @return int
     */
    public function getMinReleases(): int
    {
        return $this->getData(self::MIN_RELEASES);
    }

    /**
     * Set minimum releases
     *
     * @param int $minReleases
     * @return FrequencyProfileInterface
     */
    public function setMinReleases(int $minReleases): FrequencyProfileInterface
    {
        return $this->setData(self::MIN_RELEASES, $minReleases);
    }

    /**
     * Get maximum releases
     *
     * @return int
     */
    public function getMaxReleases(): int
    {
        return $this->getData(self::MAX_RELEASES);
    }

    /**
     * Set maximum releases
     *
     * @param int $maxReleases
     * @return FrequencyProfileInterface
     */
    public function setMaxReleases(int $maxReleases): FrequencyProfileInterface
    {
        return $this->setData(self::MAX_RELEASES, $maxReleases);
    }
}
