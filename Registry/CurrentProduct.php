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
    private ProductInterface $product;

    /**
     * @var ProductFactory
     */
    private ProductFactory $productFactory;

    /**
     * @param ProductFactory $productFactory
     */
    public function __construct(ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * Set product
     *
     * @param ProductInterface $product
     */
    public function set(ProductInterface $product): void
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return ProductInterface
     */
    public function get(): ProductInterface
    {
        return $this->product ?? $this->createNullProduct();
    }

    /**
     * Create null product
     *
     * @return ProductInterface
     */
    private function createNullProduct(): ProductInterface
    {
        return $this->productFactory->create();
    }
}
