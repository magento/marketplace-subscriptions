<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\SubscriptionItems;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

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
     * @param LoggerInterface $logger
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
        LoggerInterface $logger,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->productRepository = $productRepository;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('subscription_id', $this->request->getParam('parent_id'));
        $skus = $collection->toArray();

        foreach ($skus['items'] as $k => $item) {
            try {
                $product = $this->productRepository->getById($item['product_id']);
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical("PayPal subscription: Could not find product in admin grid.", [
                    'exception_message' => $exception->getMessage(),
                    'product_id' => $item['product_id'] ?? '',
                    'sku' => $item['sku'] ?? '',
                    'subscription_id' => 'parent_id'
                ]);
                continue;
            }
            $skus['items'][$k]['name'] = $product->getName() ?? '';
            $skus['items'][$k]['price'] = $this->subscriptionHelper->formatPrice((float) $item['price']);
        }

        return $skus;
    }
}
