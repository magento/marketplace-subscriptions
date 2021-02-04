<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Order\Create\Items;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\FrequencyProfile;

class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
{
    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfileRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\GiftMessage\Model\Save $giftMessageSave
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param SerializerInterface $serializer
     * @param SubscriptionHelper $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\GiftMessage\Model\Save $giftMessageSave,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        SerializerInterface $serializer,
        SubscriptionHelper $subscriptionHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $wishlistFactory,
            $giftMessageSave,
            $taxConfig,
            $taxData,
            $messageHelper,
            $stockRegistry,
            $stockState,
            $data
        );
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isSubscriptionAvailable(Item $item): bool
    {
        $product = $item->getProduct();
        return (int) $product->getData(SubscriptionHelper::SUB_AVAILABLE) === 1;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isSubscriptionOnly(Item $item): bool
    {
        $product = $item->getProduct();
        return (int) $product->getData(SubscriptionHelper::SUB_ONLY) === 1;
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getFrequencyProfileOptions(Item $item): array
    {
        try {
            $product = $item->getProduct();
            $frequencyProfileId = $product->getData(SubscriptionHelper::SUB_FREQ_PROF);
            /** @var FrequencyProfile $frequencyProfile */
            $frequencyProfile = $this->frequencyProfileRepository->getById((int) $frequencyProfileId);
            return $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * @param Item $item
     * @return float|int|null
     */
    public function getSubscriptionPrice(Item $item)
    {
        $product = $item->getProduct();

        if ($this->getSubscriptionPriceType($item) === SubscriptionHelper::FIXED_PRICE) {
            return $this->getSubscriptionPriceValue($item);
        }

        if ($this->getSubscriptionPriceType($item) === SubscriptionHelper::DISCOUNT_PRICE) {
            return $this->subscriptionHelper->getDiscountedPrice(
                $this->getSubscriptionPriceValue($item),
                (float) $product->getPrice()
            );
        }
    }

    /**
     * @param Item $item
     * @return int|null
     */
    public function getSubscriptionPriceType(Item $item): ?int
    {
        $product = $item->getProduct();
        return $product->getData(SubscriptionHelper::SUB_PRICE_TYPE) !== null
            ? (int) $product->getData(SubscriptionHelper::SUB_PRICE_TYPE)
            : null;
    }

    /**
     * @param Item $item
     * @return float|null
     */
    public function getSubscriptionPriceValue(Item $item): ?float
    {
        $product = $item->getProduct();
        return $product->getData(SubscriptionHelper::SUB_PRICE_VALUE) !== null
            ? (float) $product->getData(SubscriptionHelper::SUB_PRICE_VALUE)
            : null;
    }

    /**
     * @param $item
     * @return string
     */
    public function getFrequencyProfileOptionsHtml(Item $item): string
    {
        $options = $this->getFrequencyProfileOptions($item);

        if (empty($options)) {
            return '';
        }

        $html = '<select id="paypal-subscription-frequency-option" name="item['.$item->getId().'][frequency_option]">';
        $html .= '<option value="">No Thanks</option>';

        foreach ($options as $option) {
            $selected = '';
            if ($item->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL) &&
                $item->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL)->getValue() === $option['interval']) {
                $selected = 'selected';
            }
            $html .= "<option value='{$option['interval']}' {$selected}>{$option['name']}</option>";
        }

        $html .= '</select>';

        return $html;
    }
}
