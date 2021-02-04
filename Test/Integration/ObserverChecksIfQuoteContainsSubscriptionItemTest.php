<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Integration;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PayPal\Subscription\Observer\CheckCustomerLoginObserver
 */
class ObserverChecksIfQuoteContainsSubscriptionItemTest extends TestCase
{
    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testObserverChecksIfQuoteContainsSubscriptionItem()
    {
        /** @var Product $product */
        $product = ObjectManager::getInstance()->create(Product::class);
        $productResource = ObjectManager::getInstance()->create(ProductResource::class);
        $productResource->load($product, 1);

        /** @var Quote $quote */
        $quote = ObjectManager::getInstance()->create(Quote::class);

        /** @var QuoteItem $quoteItem */
        $quoteItem = ObjectManager::getInstance()->create(QuoteItem::class);

        $quoteItem->addOption([
            'code' => 'is_subscription',
            'value' => 1
        ]);

        $quoteItem->setProduct($product);

        $quote->addItem($quoteItem);

        /** @var DataObject $result */
        $result = ObjectManager::getInstance()->create(DataObject::class);

        /** @var EventManager $eventManager */
        $eventManager = ObjectManager::getInstance()->create(EventManager::class);

        $eventManager->dispatch(
            'checkout_allow_guest',
            ['quote' => $quote, 'store' => $quote->getStoreId(), 'result' => $result]
        );

        $this->assertFalse($result->getIsAllowed());
    }
}
