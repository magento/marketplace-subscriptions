<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\FrequencyProfile;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile\Collection;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $frequencyProfileCollectionFactory
     * @param SerializerInterface $serializer
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $frequencyProfileCollectionFactory,
        SerializerInterface $serializer,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $frequencyProfileCollectionFactory->create();
        $this->serializer = $serializer;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $profile) {
            $this->loadedData[$profile->getId()] = $profile->getData();
            $this->loadedData[$profile->getId()]['frequency_options'] = $this->serializer->unserialize(
                $profile->getData('frequency_options')
            );
        }
        return $this->loadedData;
    }
}
