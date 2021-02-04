<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Integration;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\ObjectManager;
use PayPal\Subscription\Registry\CurrentProduct;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PayPal\Subscription\Registry\CurrentProduct
 * @magentoDbIsolation enabled
 */
class CurrentProductRegistryTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCanSetAndGetRegistry()
    {
        $product = ObjectManager::getInstance()->create(Product::class);
        $productResource = ObjectManager::getInstance()->create(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productResource->load($product, 1);

        $currentProductRegistry = ObjectManager::getInstance()->create(CurrentProduct::class);
        $currentProductRegistry->set($product);

        /** @var Product $productFromRegistry */
        $productFromRegistry = $currentProductRegistry->get();

        $this->assertSame($product->getName(), $productFromRegistry->getName());
    }
}
