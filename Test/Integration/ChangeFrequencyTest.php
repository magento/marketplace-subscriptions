<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Integration;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\TestFramework\ObjectManager;
use PayPal\Subscription\Model\GuestQuoteManagement;
use PayPal\Subscription\Model\QuoteManagement;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PayPal\Subscription\Model\QuoteManagement
 * @magentoDbIsolation enabled
 */
class ChangeFrequencyTest extends TestCase
{
    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCanChangeFrequencyOptionForGuest()
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
            'code' => 'frequency_option_interval',
            'value' => 7
        ]);

        $quoteItem->setProduct($product)->setQuote($quote);

        $quote->setCustomerIsGuest(true)
            ->setStoreId(1)
            ->addItem($quoteItem);

        ObjectManager::getInstance()->create(\Magento\Quote\Model\ResourceModel\Quote::class)->save($quote);

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = ObjectManager::getInstance()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->setQuoteId($quote->getId());
        $quoteIdMask->setDataChanges(true);
        $quoteIdMask->save();

        /** @var Quote\Item $item */
        $item = $quote->getItemsCollection()->getFirstItem();

        /** @var GuestQuoteManagement $guestQuoteManagement */
        $guestQuoteManagement = ObjectManager::getInstance()->create(GuestQuoteManagement::class);
        /** @var QuoteItem\Option $updatedItem */
        $updatedItem = $guestQuoteManagement->changeFrequency(
            $quoteIdMask->getMaskedId(),
            (int) $item->getId(),
            14
        );

        $this->assertSame('14', $updatedItem->getValue());
    }
}
