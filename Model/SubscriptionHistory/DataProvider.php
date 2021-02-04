<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\SubscriptionHistory;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('subscription_id', $this->request->getParam('parent_id'));
        return $collection->toArray();
    }
}
