<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\ViewModel\Order\Create\Items;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\FrequencyProfile;

class Grid implements ArgumentInterface
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
     * @param PriceCurrencyInterface $priceCurrency
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param SerializerInterface $serializer
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        SerializerInterface $serializer,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * Check subscription available
     *
     * @param Item $item
     * @return bool
     */
    public function isSubscriptionAvailable(Item $item): bool
    {
        $product = $item->getProduct();
        return (int) $product->getData(SubscriptionHelper::SUB_AVAILABLE) === 1;
    }

    /**
     * Check product is available for subscription only
     *
     * @param Item $item
     * @return bool
     */
    public function isSubscriptionOnly(Item $item): bool
    {
        $product = $item->getProduct();
        return (int) $product->getData(SubscriptionHelper::SUB_ONLY) === 1;
    }

    /**
     * Get frequency profile options
     *
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
     * Get subscription price
     *
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

        return null;
    }

    /**
     * Get subscription price type
     *
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
     * Get subscription price value
     *
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
     * Get frequency profile options html
     *
     * @param Item $item
     * @return string
     */
    public function getFrequencyProfileOptionsHtml(Item $item): string
    {
        $options = $this->getFrequencyProfileOptions($item);

        if (empty($options)) {
            return '';
        }

        $html = '<select
            id="paypal-subscription-frequency-option"
            name="item[' . $item->getId() . '][frequency_option]">';
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
