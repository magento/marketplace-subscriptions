<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PayPal\Subscription\Api\SubscriptionItemRepositoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\Payment\PaymentMethodPoolInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\Collection as SubscriptionItemCollection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use PayPal\Subscription\Model\Subscription\QuoteAddressResolverInterface;

class CreateSubscriptionQuote implements CreateSubscriptionQuoteInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var PaymentMethodPoolInterface
     */
    private $paymentMethodPool;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CartInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var QuoteAddressResolverInterface
     */
    private $subscriptionQuoteAddressResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * CreateSubscriptionQuote constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param PaymentMethodPoolInterface $paymentMethodPool
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CartInterfaceFactory $quoteFactory
     * @param QuoteResource $quoteResource
     * @param SerializerInterface $serializer
     * @param QuoteAddressResolverInterface $subscriptionQuoteAddressResolver
     * @param StoreManagerInterface $storeManager
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     * @param ConfigurationInterface $configuration
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurableType
     * @param DataObjectFactory $dataObjectFactory
     * @param SubscriptionHelper $helper
     * @param SubscriptionItemRepositoryInterface $subscriptionItemRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        PaymentMethodPoolInterface $paymentMethodPool,
        ProductCollectionFactory $productCollectionFactory,
        CartInterfaceFactory $quoteFactory,
        QuoteResource $quoteResource,
        SerializerInterface $serializer,
        QuoteAddressResolverInterface $subscriptionQuoteAddressResolver,
        StoreManagerInterface $storeManager,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory,
        ConfigurationInterface $configuration,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        DataObjectFactory $dataObjectFactory,
        private readonly SubscriptionHelper $helper,
        private readonly SubscriptionItemRepositoryInterface $subscriptionItemRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->paymentMethodPool = $paymentMethodPool;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->serializer = $serializer;
        $this->subscriptionQuoteAddressResolver = $subscriptionQuoteAddressResolver;
        $this->storeManager = $storeManager;
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
        $this->configuration = $configuration;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        SubscriptionInterface $subscription
    ): CartInterface {
        /** @var SubscriptionItemCollection $subscriptionItemCollection */
        $subscriptionItemCollection = $this->subscriptionItemCollectionFactory->create();
        $subscriptionItemCollection->addFieldToFilter(
            SubscriptionItemInterface::SUBSCRIPTION_ID,
            $subscription->getId()
        );
        $subscriptionItems = $subscriptionItemCollection->getItems() ?: [];
        if ($subscriptionItems === []) {
            throw new LocalizedException(__(
                'No Subscription Items found for Subscription ID %1',
                $subscription->getId()
            ));
        }
        /** @var StoreInterface|Store $store */
        $store = $this->getOriginalOrderStore($subscription);
        $this->storeManager->setCurrentStore($store);
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->getById(
            $subscription->getCustomerId()
        );
        /** @var CartInterface|Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setCustomer($customer);
        $quote->setIsActive(false);
        $quote->setData('is_subscription_release', true);
        try {
            $this->addProductsToQuote(
                $quote,
                $subscriptionItems,
                $subscription
            );
        } catch (LocalizedException $e) {
            $currentStockFailures = $subscription->getStockFailures() ?: 0;
            $newStockFailures = $currentStockFailures+1;
            $subscription->setStockFailures($newStockFailures);
            throw $e;
        }
        $billingAddress = $this->subscriptionQuoteAddressResolver->execute($subscription);
        $quote->setBillingAddress($billingAddress);
        $shippingAddress = $this->subscriptionQuoteAddressResolver->execute(
            $subscription,
            SubscriptionInterface::SHIPPING_ADDRESS
        );
        $quote->setShippingAddress($shippingAddress);
        $this->addShippingMethodToQuote(
            $quote,
            $subscription
        );
        $this->addPaymentToQuote(
            $quote,
            $subscription
        );
        $quote->setData(
            'is_subscription_release',
            1
        );
        $quote->collectTotals();
        $quote->save();
        return $quote;
    }

    /**
     * Add Products to Quote from Subscription Items
     *
     * @param CartInterface|Quote $quote
     * @param SubscriptionItemInterface|SubscriptionItem[] $subscriptionItems
     * @param SubscriptionInterface $subscription
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addProductsToQuote(
        CartInterface|Quote $quote,
        array $subscriptionItems,
        SubscriptionInterface $subscription
    ): void {
        $subscriptionItemProductData = [];
        $productIds = [];
        $autoUpdate = $this->configuration->getAutoUpdatePrice(
            ScopeInterface::SCOPE_STORE,
            $quote->getStore()->getCode()
        );
        foreach ($subscriptionItems as $subscriptionItem) {
            $productId = $subscriptionItem->getProductId();
            if (!in_array($productId, $productIds)) {
                $productIds[] = $subscriptionItem->getProductId();
            }
            $subscriptionItemProductData[$subscriptionItem->getId()] = [
                ProductInterface::PRICE => $subscriptionItem->getPrice(),
                CartItemInterface::KEY_QTY => $subscriptionItem->getQty(),
                ProductInterface::SKU => $subscriptionItem->getSku()
            ];
        }
        if ($productIds !== []) {
            /** @var ProductCollection $productCollection */
            $productCollection = $this->productCollectionFactory->create();
            // TODO - Confirm required attribute list for sales submissions rather than all
            $productCollection->addAttributeToSelect('*');
            $productCollection->addIdFilter($productIds);
            /** @var ProductInterface[] $products */
            $products = $productCollection->getItems();
            $productsById = [];
            foreach ($products as $product) {
                $productsById[$product->getId()] = $product;
            }
            foreach ($subscriptionItems as $subscriptionItem) {
                $product = $productsById[$subscriptionItem->getProductId()] ?? null;
                $productQuoteData = $subscriptionItemProductData[$subscriptionItem->getId()] ?? null;
                if ($product !== null &&
                    $productQuoteData !== null) {
                    if ($autoUpdate === true) {
                        $subscriptionPrice = $this->getLatestSubscriptionPrice($product);
                        if ($subscriptionPrice !== $productQuoteData[ProductInterface::PRICE]) {
                            $subscriptionItem->setPrice($subscriptionPrice);
                            $this->subscriptionItemRepository->save($subscriptionItem);
                            $subscription->setData('price_changed', true);
                        }
                    } else {
                        // Use existing subscription price.
                        $subscriptionPrice = $productQuoteData[ProductInterface::PRICE];
                    }
                    $product->setPrice($subscriptionPrice);
                    if ($productQuoteData[ProductInterface::SKU] !== $product->getSku() &&
                        $product->getTypeId() === Configurable::TYPE_CODE) {
                        $this->addConfigProductToQuote(
                            $quote,
                            $subscription,
                            $product,
                            $productQuoteData
                        );
                    } else {
                        $quote->addProduct(
                            $product,
                            $productQuoteData[CartItemInterface::KEY_QTY]
                        );
                    }
                }
            }
        }
    }

    /**
     * Handle Adding Configurable Product to Quote with child options
     *
     * @param CartInterface|Quote $quote
     * @param SubscriptionInterface $subscription
     * @param ProductInterface $parentProduct
     * @param array $productQuoteData
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addConfigProductToQuote(
        CartInterface|Quote $quote,
        SubscriptionInterface $subscription,
        ProductInterface $parentProduct,
        array $productQuoteData
    ): void {
        $superAttribute = [];
        /** @var ProductInterface|Product $childProduct */
        $childProduct = $this->productRepository->get(
            $productQuoteData[ProductInterface::SKU]
        );
        $configurableAttributes = $this->configurableType->getConfigurableAttributesAsArray(
            $parentProduct
        ) ?: [];
        foreach ($configurableAttributes as $configurableAttribute) {
            $superAttribute[$configurableAttribute['attribute_id']] = $childProduct->getData(
                $configurableAttribute['attribute_code']
            );
        }
        $requestObject = $this->dataObjectFactory->create();
        $requestObject->setData(
            'qty',
            $productQuoteData[CartItemInterface::KEY_QTY]
        );
        $requestObject->setData(
            'product',
            $parentProduct->getId()
        );
        $requestObject->setData(
            'super_attribute',
            $superAttribute
        );
        $quoteItem = $quote->addProduct(
            $parentProduct,
            $requestObject
        );
        $quote->save();
        $subscriptionItemPrice = $productQuoteData[ProductInterface::PRICE] ?? null;
        if ($subscriptionItemPrice !== null) {
            $quoteItem->setPrice($subscriptionItemPrice);
            $quoteItem->setCustomPrice($subscriptionItemPrice);
            $quoteItem->setOriginalCustomPrice($subscriptionItemPrice);
            $quoteItem->save();
        }
    }

    /**
     * Set Shipping Method on Quote shipping address and collect rates
     *
     * @param CartInterface $quote
     * @param SubscriptionInterface $subscription
     */
    private function addShippingMethodToQuote(
        CartInterface $quote,
        SubscriptionInterface $subscription
    ): void {
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $this->configuration->getReleaseShippingMethod() ?: $subscription->getShippingMethod();
        $shippingAddress->setShippingMethod($shippingMethod);
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
    }

    /**
     * Return Store From Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return StoreInterface
     */
    private function getOriginalOrderStore(
        SubscriptionInterface $subscription
    ): StoreInterface {
        $originalOrderId = $subscription->getOrderId();
        /** @var OrderInterface|Order $order */
        $order = $this->orderRepository->get($originalOrderId);
        return $order->getStore();
    }

    /**
     * Set Payment Method on Quote
     *
     * @param CartInterface $quote
     * @param SubscriptionInterface $subscription
     * @throws LocalizedException
     * @throws InputException
     */
    private function addPaymentToQuote(
        CartInterface $quote,
        SubscriptionInterface $subscription
    ): void {
        $payment = $this->paymentMethodPool->getByPaymentMethod(
            $subscription->getPaymentMethod()
        );
        $paymentData = $this->serializer->unserialize(
            $subscription->getPaymentData()
        );
        $payment->execute(
            $quote,
            $paymentData
        );
    }

    /**
     * Get latest subscription price
     *
     * @param ProductInterface $product
     * @return float
     * @throws LocalizedException
     */
    private function getLatestSubscriptionPrice(ProductInterface $product): float
    {
        $priceType = $product->getData(SubscriptionHelper::SUB_PRICE_TYPE) !== null
            ? (int) $product->getData(SubscriptionHelper::SUB_PRICE_TYPE)
            : null;
        $priceValue = $product->getData(SubscriptionHelper::SUB_PRICE_VALUE) !== null
            ? (float) $product->getData(SubscriptionHelper::SUB_PRICE_VALUE)
            : null;

        if ($priceValue === null) {
            throw new LocalizedException(
                __("Product %1 has no subscription price value defined", $product->getId())
            );
        }

        if ($priceType === SubscriptionHelper::FIXED_PRICE) {
            return $priceValue;
        } elseif ($priceType === SubscriptionHelper::DISCOUNT_PRICE) {
            $discountedPrice = $this->helper->getDiscountedPrice(
                $priceValue,
                (float) $product->getPrice()
            );
            return $discountedPrice;
        }
    }
}
