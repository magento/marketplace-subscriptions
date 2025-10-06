<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\CustomerData\DefaultItem as Subject;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\ConfigurationInterface;
use PayPal\Subscription\Model\FrequencyProfile;

class CartItemData
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfileRepository;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * CartItemData constructor
     *
     * @param Session $checkoutSession
     * @param ConfigurationInterface $configuration
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param PricingHelper $pricingHelper
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        Session $checkoutSession,
        ConfigurationInterface $configuration,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        PricingHelper $pricingHelper,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configuration = $configuration;
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->pricingHelper = $pricingHelper;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * Get item data
     *
     * @param Subject $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetItemData(
        Subject $subject,
        array $result
    ): array {
        if ($this->configuration->getActive() === false) {
            return $result;
        }
        if ($this->isSubscriptionDataAlreadySet($result) === true) {
            return $result;
        }
        $itemId = $result['item_id'] ?? null;
        if ($itemId !== null) {
            $quote = $this->checkoutSession->getQuote();
            $quoteItem = $quote->getItemById($itemId);
            if ($quoteItem !== false) {
                $isSubscription = false;
                $frequencyOptionInterval = null;
                $isSubscriptionOption = $quoteItem->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION);
                $frequencyOptionIntervalOption = $quoteItem->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL);
                if ($isSubscriptionOption !== null) {
                    $isSubscription = $isSubscriptionOption->getValue() === '1';
                }
                if ($frequencyOptionIntervalOption !== null) {
                    $frequencyOptionInterval = (int) $frequencyOptionIntervalOption->getValue();
                }
                if ($isSubscription === true &&
                    $frequencyOptionInterval != null) {
                    $product = $quoteItem->getProduct();
                    $productFrequencyProfileId = $product->getData(SubscriptionHelper::SUB_FREQ_PROF);
                    if ($productFrequencyProfileId) {
                        /** @var FrequencyProfileInterface|FrequencyProfile $frequencyProfile */
                        $frequencyProfile = $this->frequencyProfileRepository->getById(
                            (int) $productFrequencyProfileId
                        );
                        $intervalOptions = $frequencyProfile->getFrequencyOptions();
                        $productSubscriptionPriceType = (int) $product->getData(SubscriptionHelper::SUB_PRICE_TYPE);
                        $productSubscriptionPriceValue = $product->getData(SubscriptionHelper::SUB_PRICE_VALUE);
                        $subscriptionData = [];
                        $subscriptionData[SubscriptionHelper::IS_SUBSCRIPTION] = true;
                        $subscriptionData[SubscriptionHelper::FREQ_OPT_INTERVAL] = $frequencyOptionInterval;
                        $subscriptionData[SubscriptionHelper::FREQ_OPT_INTERVAL_OPTIONS] = $intervalOptions;
                        $subscriptionData[SubscriptionHelper::SUB_PRICE_TYPE] = $productSubscriptionPriceType;
                        $subscriptionData[SubscriptionHelper::SUB_PRICE_VALUE] =
                            $this->pricingHelper->currency(
                                round((float)$productSubscriptionPriceValue, 1),
                                true,
                                false
                            );
                        $subscriptionData[SubscriptionHelper::SUB_PRICE_SAVING] = $this->getSaving(
                            $product,
                            $productSubscriptionPriceType,
                            (float) $productSubscriptionPriceValue
                        );
                        $result = array_merge(
                            $result,
                            $subscriptionData
                        );
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Returns the saving amount for a user
     *
     * @param Product $product
     * @param int $subscriptionPriceType
     * @param float $subscriptionPriceValue
     * @return string
     */
    private function getSaving(
        Product $product,
        int $subscriptionPriceType,
        float $subscriptionPriceValue
    ): string {
        $discountedPrice = $subscriptionPriceType == SubscriptionHelper::DISCOUNT_PRICE ?
            $this->subscriptionHelper->getDiscountedPrice(
                (float) $subscriptionPriceValue,
                (float) $product->getFinalPrice()
            ) :
            $subscriptionPriceValue;
        $discount = (float) $product->getFinalPrice() - $discountedPrice;
        return $this->pricingHelper->currency(
            $discount,
            true,
            false
        );
    }

    /**
     * Check whether Subscription Data is already set in Cart Item Data array
     *
     * @param array $cartItemData
     * @return bool
     */
    private function isSubscriptionDataAlreadySet(
        array $cartItemData
    ): bool {
        $cartItemDataKeys = array_keys($cartItemData);
        $diff = array_diff(
            [
                SubscriptionHelper::IS_SUBSCRIPTION,
                SubscriptionHelper::FREQ_OPT_INTERVAL,
                SubscriptionHelper::FREQ_OPT_INTERVAL_OPTIONS,
                SubscriptionHelper::SUB_PRICE_TYPE,
                SubscriptionHelper::SUB_PRICE_VALUE,
                SubscriptionHelper::SUB_PRICE_SAVING
            ],
            $cartItemDataKeys
        );
        return $diff === [];
    }
}
