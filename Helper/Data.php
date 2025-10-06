<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Helper;

use InvalidArgumentException;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Store\Model\ScopeInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\FrequencyProfileRepository;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use PayPal\Subscription\Model\Subscription;
use PayPal\Subscription\Model\Subscription\QuoteAddressResolverInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Data extends AbstractHelper
{
    private const CONFIG_PREFIX = 'paypal/subscriptions/';

    // Attribute value to define price as fixed
    public const FIXED_PRICE = 0;
    // Attribute value to define price as a discount
    public const DISCOUNT_PRICE = 1;

    public const SUB_AVAILABLE = 'subscription_available';
    public const SUB_ONLY = 'subscription_only';

    public const SUB_PRICE_TYPE = 'subscription_price_type';
    public const SUB_PRICE_VALUE = 'subscription_price_value';
    public const SUB_PRICE_SAVING = 'subscription_price_saving';

    public const SUB_FREQ_PROF = 'subscription_frequency_profile';
    public const SUB_FREQ_REC = 'recommended_frequency';
    public const SUB_FREQ_REASON = 'recommended_frequency_reason';

    // Quote Item Options
    public const IS_SUBSCRIPTION = 'is_subscription';
    public const FREQ_OPT_INTERVAL = 'frequency_option_interval';
    public const FREQ_OPT_INTERVAL_LABEL = 'frequency_option_interval_label';
    public const FREQ_OPT_INTERVAL_OPTIONS = 'frequency_options';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var FrequencyProfileRepository
     */
    private $frequencyProfileRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;
    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollectionFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var QuoteResource
     */
    private $quoteResource;
    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;
    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var QuoteAddressResolverInterface
     */
    private $quoteAddressResolver;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepository $productRepository
     * @param FrequencyProfileRepository $frequencyProfileRepository
     * @param SerializerInterface $serializer
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartManagementInterface $cartManagement
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteResource $quoteResource
     * @param AddressInterfaceFactory $addressFactory
     * @param PricingHelper $pricingHelper
     * @param QuoteAddressResolverInterface $quoteAddressResolver
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ProductRepository $productRepository,
        FrequencyProfileRepository $frequencyProfileRepository,
        SerializerInterface $serializer,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CartManagementInterface $cartManagement,
        CartRepositoryInterface $cartRepository,
        QuoteResource $quoteResource,
        AddressInterfaceFactory $addressFactory,
        PricingHelper $pricingHelper,
        QuoteAddressResolverInterface $quoteAddressResolver
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->quoteResource = $quoteResource;
        $this->addressFactory = $addressFactory;
        $this->pricingHelper = $pricingHelper;
        $this->quoteAddressResolver = $quoteAddressResolver;
    }

    /**
     * Check subscription active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'active',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get serialised address
     *
     * @param AddressInterface|OrderAddressInterface $address
     * @return string
     */
    public function getSerialisedAddress($address): string
    {
        return $this->serializer->serialize(
            [
                'firstname' => $address !== null ? $address->getFirstname() : '',
                'lastname' => $address !== null ? $address->getLastname() : '',
                'company' => $address !== null ? $address->getCompany() : '',
                'street' => $address !== null ? $address->getStreet() : '',
                'city' => $address !== null ? $address->getCity() : '',
                'region' => $address !== null ? $address->getRegion() : '',
                'region_id' => $address !== null ? $address->getRegionId() : '',
                'postcode' => $address !== null ? $address->getPostcode() : '',
                'country_id' => $address !== null ? $address->getCountryId() : '',
                'telephone' => $address !== null ? $address->getTelephone() : '',
            ]
        );
    }

    /**
     * Get formatted address
     *
     * @param string $address
     * @return string
     */
    public function getFormattedAddress(string $address)
    {
        try {
            $address = $this->serializer->unserialize($address);
            if ($address['telephone']) {
                $address['telephone'] = 'T: ' . $address['telephone'];
            }
            unset($address['region_id']);
            $formattedAddress = new RecursiveIteratorIterator(new RecursiveArrayIterator($address));
            return implode(', ', array_filter(iterator_to_array($formattedAddress, false)));
        } catch (InvalidArgumentException $e) {
            return $address;
        }
    }

    /**
     * Get interval label
     *
     * @param int $productId
     * @param int $interval
     * @return string
     */
    public function getIntervalLabel(int $productId, int $interval): string
    {
        try {
            $product = $this->productRepository->getById($productId);
            $frequencyProfile = $this->frequencyProfileRepository->getById(
                (int) $product->getData('subscription_frequency_profile')
            );
            $intervalOptions = $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
            foreach ($intervalOptions as $option) {
                if ((int) $option['interval'] === $interval) {
                    return $option['name'];
                }
            }
        } catch (NoSuchEntityException $e) {
            return '';
        }

        return '';
    }

    /**
     * Get Discounted price
     *
     * @param float $discount
     * @param float $price
     * @return float
     */
    public function getDiscountedPrice(
        float $discount,
        float $price
    ): float {
        return ((100 - $discount) / 100) * $price;
    }

    /**
     * Get Status Label from value
     *
     * @param int $status
     * @return string
     */
    public function getStatusLabel($status): string
    {
        $statusArray = [
            1 => 'active',
            2 => 'paused',
            3 => 'cancelled',
            4 => 'expired',
        ];

        return $statusArray[$status];
    }

    /**
     * Get shipping
     *
     * @param int $subscriptionId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getShipping(int $subscriptionId): array
    {
        /** @var Subscription $subscription */
        $subscription = $this->getSubscription($subscriptionId);
        $subscriptionItemsCollection = $this->subscriptionItemCollectionFactory->create();
        $subscriptionItems = $subscriptionItemsCollection->getItemsByColumnValue(
            'subscription_id',
            $subscription->getId()
        );

        $customer = $this->customerRepository->getById($subscription->getCustomerId());

        $cartId = $this->cartManagement->createEmptyCart();

        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $quote->setCustomer($customer);

        $this->addProducts($subscriptionItems, $quote);
        $shippingAddress = $this->quoteAddressResolver->execute(
            $subscription,
            SubscriptionInterface::SHIPPING_ADDRESS
        );
        $quote->setShippingAddress($shippingAddress);
        $quote->collectTotals();

        $address = $quote->getShippingAddress();
        $rates = $address->collectShippingRates();

        return $rates->getAllShippingRates();
    }

    /**
     * Get subscription
     *
     * @param int $id
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    private function getSubscription(int $id): SubscriptionInterface
    {
        return $this->subscriptionRepository->getById((int) $id);
    }

    /**
     * Get format price
     *
     * @param float $price
     * @return float|string
     */
    public function formatPrice(float $price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Get bundle data
     *
     * @param ProductInterface $subscriptionProduct
     * @return array
     */
    public function getBundleData(ProductInterface $subscriptionProduct): array
    {
        $optionData = [];
        if ($subscriptionProduct->getTypeId() !== Type::TYPE_CODE) {
            return $optionData;
        }

        /** @var Type $typeInstance */
        $typeInstance = $subscriptionProduct->getTypeInstance();
        $optionMap = $typeInstance->getOptions($subscriptionProduct);
        $bundleSelections = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($subscriptionProduct),
            $subscriptionProduct
        );

        if (empty($bundleSelections->getItems()) || $optionMap === null) {
            return $optionData;
        }

        foreach ($bundleSelections->getItems() as $childData) {
            $bundleOption = $optionMap[(int)$childData->getOptionId()] ?? null;
            if (!$bundleOption instanceof Option) {
                continue;
            }
            $optionData[$bundleOption->getTitle()][] = [
                'quantity' => $childData->getData('selection_qty') ?? '',
                'sku' => $childData->getSku(),
                'name' => $childData->getName(),
                'selection_price' =>  $this->formatPrice((float)$childData->getData('selection_price_value'))
            ];
        }

        return $optionData;
    }

    /**
     * Add products to the quote
     *
     * @param array $subscriptionItems
     * @param CartInterface $quote
     * @throws LocalizedException
     */
    private function addProducts(array $subscriptionItems, CartInterface $quote): void
    {
        /** @var SubscriptionItemInterface $item */
        foreach ($subscriptionItems as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId());
                $product->setPrice($item->getPrice());
                $quote->addProduct($product, $item->getQty());
            } catch (NoSuchEntityException $e) {
                // @codingStandardsIgnoreStart
                throw new LocalizedException(__('Could not find product: %1', $e->getMessage()));
            } catch (LocalizedException $e) {
                throw new LocalizedException(__('Could not add product to quote: %1', $e->getMessage()));
                // @codingStandardsIgnoreEnd
            }
        }
    }
}
