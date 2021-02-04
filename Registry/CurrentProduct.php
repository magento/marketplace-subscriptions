<?php

declare(strict_types=1);

namespace PayPal\Subscription\Registry;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory as ProductFactory;

class CurrentProduct
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    public function __construct(ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * @param ProductInterface $product
     */
    public function set(ProductInterface $product): void
    {
        $this->product = $product;
    }

    /**
     * @return ProductInterface
     */
    public function get(): ProductInterface
    {
        return $this->product ?? $this->createNullProduct();
    }

    /**
     * @return ProductInterface
     */
    private function createNullProduct(): ProductInterface
    {
        return $this->productFactory->create();
    }
}
