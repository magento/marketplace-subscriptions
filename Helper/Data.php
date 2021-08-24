<?php

declare(strict_types=1);

namespace PayPal\Subscription\Helper;

use InvalidArgumentException;
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
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\FrequencyProfileRepository;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use PayPal\Subscription\Model\Subscription;
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

    public const SUB_FREQ_PROF = 'subscription_frequency_profile';

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
        PricingHelper $pricingHelper
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
    }

    /**
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
     * @return bool
     */
    public function getCountOfFailedAttempts(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'failed_payments',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isCronActive(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::CONFIG_PREFIX . 'cron_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param AddressInterface|OrderAddressInterface $address
     * @return string
     */
    public function getSerialisedAddress($address): string
    {
        return $this->serializer->serialize(
            [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'company' => $address->getCompany(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'region' => $address->getRegion(),
                'region_id' => $address->getRegionId(),
                'postcode' => $address->getPostcode(),
                'country_id' => $address->getCountryId(),
                'telephone' => $address->getTelephone(),
            ]
        );
    }

    /**
     * @param $address
     * @return string
     */
    public function getFormattedAddress($address)
    {
        try {
            $address = $this->serializer->unserialize($address);
            $formattedAddress = new RecursiveIteratorIterator(new RecursiveArrayIterator($address));
            return implode(', ', array_filter(iterator_to_array($formattedAddress, false)));
        } catch (InvalidArgumentException $e) {
            return $address;
        }
    }

    /**
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
     * @param float $discount
     * @param float $price
     * @return float
     */
    public function getDiscountedPrice(float $discount, float $price): float
    {
        return ((100 - $discount) / 100) * $price;
    }

    /**
     * Get Status Label from value
     * @param $status
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

    public function getShipping($subscriptionId)
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

        $quote->setShippingAddress($this->setAddress($subscription->getShippingAddress()));
        $quote->collectTotals();

        return [];
    }

    private function getSubscription($id)
    {
        return $this->subscriptionRepository->getById((int) $id);
    }

    public function formatPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    private function setAddress(string $address)
    {
        $subscriptionAddress = $this->serializer->unserialize($address);
        $newAddress = $this->addressFactory->create();
        $newAddress->setFirstname($subscriptionAddress['firstname'])
            ->setLastname($subscriptionAddress['lastname'])
            ->setCompany($subscriptionAddress['company'] ?? null)
            ->setStreet($subscriptionAddress['street'])
            ->setCity($subscriptionAddress['city'])
            ->setRegion($subscriptionAddress['region'] ?? null)
            ->setRegionId($subscriptionAddress['region_id'] ?? null)
            ->setCountryId($subscriptionAddress['country_id'])
            ->setPostcode($subscriptionAddress['postcode'] ?? null)
            ->setTelephone($subscriptionAddress['telephone']);
        return $newAddress;
    }

    /**
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
                throw new LocalizedException(__('Could not find product: %1', $e->getMessage()));
            } catch (LocalizedException $e) {
                throw new LocalizedException(__('Could not add product to quote: %1', $e->getMessage()));
            }
        }
    }
}
