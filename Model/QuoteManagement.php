<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Item as ItemResource;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option as OptionResource;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory as QuoteItemOptionCollection;
use PayPal\Subscription\Api\QuoteManagementInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class QuoteManagement implements QuoteManagementInterface
{
    /**
     * @var CartItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ItemResource
     */
    private $itemResource;

    /**
     * @var QuoteItemOptionCollection
     */
    private $itemOptionCollection;

    /**
     * @var OptionResource
     */
    private $optionResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * QuoteManagement constructor.
     *
     * @param CartItemInterfaceFactory $itemFactory
     * @param ItemResource $itemResource
     * @param QuoteItemOptionCollection $itemOptionCollection
     * @param OptionResource $optionResource
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        CartItemInterfaceFactory $itemFactory,
        ItemResource $itemResource,
        QuoteItemOptionCollection $itemOptionCollection,
        OptionResource $optionResource,
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource
    ) {
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->itemOptionCollection = $itemOptionCollection;
        $this->optionResource = $optionResource;
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
    }

    /**
     * @param int|string $cartId
     * @param int $quoteItemId
     * @param int $frequency
     * @return CartInterface
     * @throws LocalizedException
     */
    public function changeFrequency($cartId, int $quoteItemId, int $frequency): CartInterface
    {
        /** @var Item $quoteItem */
        $quoteItem = $this->itemFactory->create();
        $this->itemResource->load($quoteItem, $quoteItemId);

        if (!$quoteItem->getId()) {
            throw new LocalizedException(__('Unable to fetch quote item.'));
        }

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->loadByIdWithoutStore($quote, $cartId);

        $itemOptions = $this->itemOptionCollection->create()->getOptionsByItem($quoteItem);

        /** @var Option $option */
        foreach ($itemOptions as $option) {
            if ($option->getCode() === SubscriptionHelper::FREQ_OPT_INTERVAL) {
                $option->setValue((string) $frequency);
                try {
                    $this->optionResource->save($option);
                } catch (AlreadyExistsException | Exception $e) {
                    throw new LocalizedException(__('Unable to save quote item.'));
                }
            }
        }

        return $quote;
    }
}
