<?php

declare(strict_types=1);

namespace PayPal\Subscription\ViewModel\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class Item implements ArgumentInterface
{
    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfile;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * Item constructor.
     * @param FrequencyProfileRepositoryInterface $frequencyProfile
     * @param SerializerInterface $serializer
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        FrequencyProfileRepositoryInterface $frequencyProfile,
        SerializerInterface $serializer,
        PricingHelper $pricingHelper
    ) {
        $this->frequencyProfile = $frequencyProfile;
        $this->serializer = $serializer;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param QuoteItem $item
     * @return bool
     */
    public function hasSubscription(QuoteItem $item): bool
    {
        $isSubscription = $item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION);
        return $isSubscription ? (bool) $isSubscription->getValue() : false;
    }

    /**
     * @param QuoteItem $item
     * @return mixed
     */
    public function getFrequencyInterval(QuoteItem $item)
    {
        $interval = $item->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL);
        return $interval->getValue();
    }

    /**
     * @param QuoteItem $item
     * @return array
     */
    public function getFrequencyProfileOptions(QuoteItem $item): array
    {
        // Get product ID from item
        $product = $item->getProduct();

        try {
            $frequencyProfileId = $product->getCustomAttribute('subscription_frequency_profile');
            $frequencyProfile = $this->frequencyProfile->getById((int) $frequencyProfileId->getValue());
            return $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * @param $item
     * @param $option
     * @return bool
     */
    public function getSelectedFrequency(QuoteItem $item, $option): bool
    {
        return (bool) (
            $item->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL)->getValue() === $option['interval']
        );
    }

    /**
     * @return bool
     */
    public function isSubscriptionOnly(QuoteItem $item): bool
    {
        return (bool) $item->getProduct()->getData(SubscriptionHelper::SUB_ONLY);
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return $this->pricingHelper->currency($price, true, false);
    }
}
