<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile\CollectionFactory;

class Frequency extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    private $frequencyProfileCollection;

    /**
     * FrequencyProfile constructor.
     *
     * @param CollectionFactory $frequencyProfileCollection
     */
    public function __construct(
        CollectionFactory $frequencyProfileCollection
    ) {
        $this->frequencyProfileCollection = $frequencyProfileCollection;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $collection = $this->frequencyProfileCollection->create()->getItems();

        $this->_options[] = ['label' => __('Please select a profile'), 'value' => ''];

        foreach ($collection as $item) {
            $this->_options[] = ['label' => $item->getName(), 'value' => $item->getId()];
        }

        return $this->_options;
    }
}
