<?php

declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory as QuoteItemOptionCollection;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;

/**
 * Plugin to add subscription data to cartData object.
 */
class CartItemData
{
    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfile;

    /**
     * @var CartItemInterfaceFactory
     */
    private $quoteItemFactory;

    /**
     * @var Item
     */
    private $itemResourceModel;

    /**
     * @var QuoteItemOptionCollection
     */
    private $collection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * CartItemData constructor.
     * @param CartItemInterfaceFactory $quoteItemFactory
     * @param Item $itemResourceModel
     * @param QuoteItemOptionCollection $collection
     * @param SerializerInterface $serializer
     * @param FrequencyProfileRepositoryInterface $frequencyProfile
     */
    public function __construct(
        CartItemInterfaceFactory $quoteItemFactory,
        Item $itemResourceModel,
        QuoteItemOptionCollection $collection,
        SerializerInterface $serializer,
        FrequencyProfileRepositoryInterface $frequencyProfile,
        ProductRepositoryInterface $productRepository
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->collection = $collection;
        $this->serializer = $serializer;
        $this->frequencyProfile = $frequencyProfile;
        $this->productRepository = $productRepository;
    }

    /**
     * @param DefaultItem $subject
     * @param $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetItemData(DefaultItem $subject, $result): array
    {
        $isSubscription = false;
        $frequencyOptionInterval = null;
        $subscriptionData = [];

        $quoteItem = $this->quoteItemFactory->create();
        $this->itemResourceModel->load($quoteItem, $result['item_id']);

        $quoteItemOptions = $this->collection->create()->getOptionsByItem($quoteItem);

        /** @var Option $option */
        foreach ($quoteItemOptions as $option) {
            if ($option->getValue()) {
                $optionValues = $this->serializer->unserialize($option->getValue());

                if (!is_array($optionValues) &&
                    $option->getCode() === SubscriptionHelper::IS_SUBSCRIPTION &&
                    $option->getValue() === '1') {
                    $isSubscription = true;
                }

                if (!is_array($optionValues) &&
                    $option->getCode() === SubscriptionHelper::FREQ_OPT_INTERVAL
                ) {
                    $frequencyOptionInterval = (int) $option->getValue();
                }
            }
        }

        // Get product ID from item
        $product = $this->productRepository->getById($quoteItem->getProductId());

        if ($frequencyProfileId = $product->getCustomAttribute('subscription_frequency_profile')) {

            $frequencyProfile = $this->frequencyProfile->getById((int) $frequencyProfileId->getValue());
            $intervalOptions = $frequencyProfile->getFrequencyOptions();

            if ($isSubscription && $frequencyOptionInterval) {
                $subscriptionData[SubscriptionHelper::IS_SUBSCRIPTION] = true;
                $subscriptionData[SubscriptionHelper::FREQ_OPT_INTERVAL] = $frequencyOptionInterval;
                $subscriptionData[SubscriptionHelper::FREQ_OPT_INTERVAL_OPTIONS] = $intervalOptions;
            }
        }

        return array_merge($result, $subscriptionData);
    }
}
