<?php

declare(strict_types=1);

namespace PayPal\Subscription\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Registry\CurrentProduct;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;

class Product implements ArgumentInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var CurrentProduct
     */
    protected $currentProduct;

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
     * Product constructor.
     *
     * @param CurrentProduct $currentProduct
     * @param FrequencyProfileRepositoryInterface $frequencyProfile
     * @param SerializerInterface $serializer
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        CurrentProduct $currentProduct,
        FrequencyProfileRepositoryInterface $frequencyProfile,
        SerializerInterface $serializer,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->currentProduct = $currentProduct;
        $this->frequencyProfile = $frequencyProfile;
        $this->serializer = $serializer;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @return bool
     */
    public function isSubscriptionAvailable(): bool
    {
        if (!$this->product) {
            $this->product = $this->currentProduct->get();
        }
        return (bool) $this->product->getData(SubscriptionHelper::SUB_AVAILABLE);
    }

    /**
     * @return bool
     */
    public function isSubscriptionOnly(): bool
    {
        if (!$this->product) {
            $this->product = $this->currentProduct->get();
        }
        return (bool) $this->product->getData(SubscriptionHelper::SUB_ONLY);
    }

    /**
     * 0 - Fixed Price e.g 9.99
     * 1 - Discount off of base price e.g. 75% off 10.00 is 2.50
     * @return int|null
     */
    public function getSubscriptionPriceType(): ?int
    {
        if (!$this->product) {
            $this->product = $this->currentProduct->get();
        }
        return $this->product->getData(SubscriptionHelper::SUB_PRICE_TYPE) !== null
            ? (int) $this->product->getData(SubscriptionHelper::SUB_PRICE_TYPE)
            : null;
    }

    /**
     * @return float|null
     */
    public function getSubscriptionPriceValue(): ?float
    {
        if (!$this->product) {
            $this->product = $this->currentProduct->get();
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
            $this->product = $this->currentProduct->get();
        }

        if ($this->getSubscriptionPriceType() === SubscriptionHelper::FIXED_PRICE) {
            return $this->getSubscriptionPriceValue();
        }

        if ($this->getSubscriptionPriceType() === SubscriptionHelper::DISCOUNT_PRICE) {
            return $this->subscriptionHelper->getDiscountedPrice(
                $this->getSubscriptionPriceValue(),
                (float) $this->product->getPrice()
            );
        }
    }

    /**
     * Returns the percentage saved
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
     * @return int
     */
    public function getFrequencyProfileId(): int
    {
        if (!$this->product) {
            $this->product = $this->currentProduct->get();
        }

        return (int) $this->product->getData('subscription_frequency_profile');
    }

    /**
     * @return array
     */
    public function getFrequencyProfileOptions(): array
    {
        if (!$this->product) {
            $this->product = $this->currentProduct->get();
        }

        try {
            $frequencyProfileId = $this->product->getData('subscription_frequency_profile');
            $frequencyProfile = $this->frequencyProfile->getById((int) $frequencyProfileId);
            return $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}
