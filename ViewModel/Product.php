<?php
declare(strict_types=1);

namespace PayPal\Subscription\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Setup\Patch\Data\InstallRecommendedFrequencyAttributes;
use PayPal\Subscription\Setup\Patch\Data\AddProductSubscriptionAttributes;

class Product implements ArgumentInterface
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfile;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Product constructor
     *
     * @param Registry $registry
     * @param FrequencyProfileRepositoryInterface $frequencyProfile
     * @param SerializerInterface $serializer
     * @param SubscriptionHelper $subscriptionHelper
     * @param PricingHelper $pricingHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Registry $registry,
        FrequencyProfileRepositoryInterface $frequencyProfile,
        SerializerInterface $serializer,
        SubscriptionHelper $subscriptionHelper,
        PricingHelper $pricingHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->frequencyProfile = $frequencyProfile;
        $this->serializer = $serializer;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Is subscription available?
     *
     * @return bool
     */
    public function isSubscriptionAvailable(): bool
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        return (bool) $this->product->getData(SubscriptionHelper::SUB_AVAILABLE);
    }

    /**
     * Is product subscription only?
     *
     * @return bool
     */
    public function isSubscriptionOnly(): bool
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        return (bool) $this->product->getData(SubscriptionHelper::SUB_ONLY);
    }

    /**
     * 0 - Fixed Price e.g 9.99
     * 1 - Discount off of base price e.g. 75% off 10.00 is 2.50
     *
     * @return int|null
     */
    public function getSubscriptionPriceType(): ?int
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        return $this->product->getData(SubscriptionHelper::SUB_PRICE_TYPE) !== null
            ? (int) $this->product->getData(SubscriptionHelper::SUB_PRICE_TYPE)
            : null;
    }

    /**
     * Get subscription price value
     *
     * @return float|null
     */
    public function getSubscriptionPriceValue(): ?float
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        return $this->product->getData(SubscriptionHelper::SUB_PRICE_VALUE) !== null
            ? (float) $this->product->getData(SubscriptionHelper::SUB_PRICE_VALUE)
            : null;
    }

    /**
     * Returns subscription price as a float to 4 decimal places.
     *
     * @return float|void
     */
    public function getSubscriptionPrice()
    {
        if ($this->getSubscriptionPriceType() === null || !$this->getSubscriptionPriceValue() === null) {
            return;
        }

        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        if ($this->getSubscriptionPriceType() === SubscriptionHelper::FIXED_PRICE) {
            return $this->pricingHelper->currency($this->getSubscriptionPriceValue(), true, false);
        }

        if ($this->getSubscriptionPriceType() === SubscriptionHelper::DISCOUNT_PRICE) {
            return $this->pricingHelper->currency($this->subscriptionHelper->getDiscountedPrice(
                $this->getSubscriptionPriceValue(),
                (float) $this->product->getFinalPrice()
            ), true, false);
        }
    }

    /**
     * Returns the percentage saved
     *
     * @return float
     */
    public function getPercentageSaved(): float
    {
        if ($this->getSubscriptionPriceValue()
            && $this->getSubscriptionPriceType() === SubscriptionHelper::DISCOUNT_PRICE) {
            return round($this->getSubscriptionPriceValue(), 1);
        }

        return 0;
    }

    /**
     * Returns the saving amount for a user.
     *
     * @return string
     */
    public function getSaving(): string
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        $discountedPrice = $this->getSubscriptionPriceType() == SubscriptionHelper::DISCOUNT_PRICE ?
            $this->subscriptionHelper->getDiscountedPrice(
                $this->getSubscriptionPriceValue(),
                (float) $this->product->getPrice()
            ) :
            $this->getSubscriptionPriceValue();

        $discount = (float) $this->product->getFinalPrice() - $discountedPrice;

        return $this->pricingHelper->currency(
            $discount,
            true,
            false
        );
    }

    /**
     * Get frequency profile ID
     *
     * @return int
     */
    public function getFrequencyProfileId(): int
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        return (int) $this->product->getData('subscription_frequency_profile');
    }

    /**
     * Get frequency profile options
     *
     * @return array
     */
    public function getFrequencyProfileOptions(): array
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        try {
            $frequencyProfileId = $this->product->getData('subscription_frequency_profile');
            $frequencyProfile = $this->frequencyProfile->getById((int) $frequencyProfileId);
            $options = $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
            $storeCode = $this->storeManager->getStore()->getCode();
            $storeNameIndex = 'name_' . $storeCode;
            foreach ($options as $i => $option) {
                $storeLabel = $option[$storeNameIndex] ?? null;
                if ($storeLabel != null) {
                    $options[$i]['name'] = $storeLabel;
                }
            }
            return $options;
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * Return Recommended Frequency if set
     *
     * @return int|null
     */
    public function getRecommendedFrequencyOptionId(): ?int
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        $recommendedFrequency = $this->product->getData(
            InstallRecommendedFrequencyAttributes::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE
        );
        return $recommendedFrequency === null ?
            $recommendedFrequency :
            (int) $recommendedFrequency;
    }

    /**
     * Return Recommended Frequency if set
     *
     * @return array
     */
    public function getRecommendedFrequencyOption(): array
    {
        $options = $this->getFrequencyProfileOptions();
        if ($options === []) {
            return [];
        }
        $recommendedOptionId = $this->getRecommendedFrequencyOptionId();
        if ($options === null) {
            return [];
        }
        foreach ($options as $option) {
            if ($option['interval'] == $recommendedOptionId) {
                return $option;
            }
        }
        return [];
    }

    /**
     * Return boolean on whether option is recommended
     *
     * @param array $option
     * @return bool
     */
    public function isRecommendedOption(
        array $option
    ): bool {
        $recommendedOption = $this->getRecommendedFrequencyOptionId();
        return $recommendedOption === null ?
            false :
            (int) $option['interval'] === (int) $recommendedOption;
    }
}
