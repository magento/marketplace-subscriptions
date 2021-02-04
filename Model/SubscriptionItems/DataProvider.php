<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\SubscriptionItems;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SubscriptionHelper $subscriptionHelper
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        SubscriptionHelper $subscriptionHelper,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->productRepository = $productRepository;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->request = $request;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('subscription_id', $this->request->getParam('parent_id'));
        $skus = $collection->toArray();

        foreach ($skus['items'] as $k => $item) {
            $skus['items'][$k]['name'] = $this->productRepository->get($item['sku'])->getData('name');
            $skus['items'][$k]['price'] = $this->subscriptionHelper->formatPrice($item['price']);
        }

        return $skus;
    }
}
