<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Subscriptions\Edit;

use Magento\Backend\Block\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use PayPal\Subscription\Model\Subscription;
use PayPal\Subscription\Model\Subscription\QuoteAddressResolverInterface;

class ShippingMethods extends Template
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollectionFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var QuoteAddressResolverInterface
     */
    private $quoteAddressResolver;

    /**
     * ShippingMethods constructor
     *
     * @param Template\Context $context
     * @param RequestInterface $request
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartManagementInterface $cartManagement
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteResource $quoteResource
     * @param PricingHelper $pricingHelper
     * @param QuoteAddressResolverInterface $quoteAddressResolver
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        CartManagementInterface $cartManagement,
        CartRepositoryInterface $cartRepository,
        QuoteResource $quoteResource,
        PricingHelper $pricingHelper,
        QuoteAddressResolverInterface $quoteAddressResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->quoteResource = $quoteResource;
        $this->quoteAddressResolver = $quoteAddressResolver;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Get subscription id
     *
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->request->getParam('id');
    }

    /**
     * Get subscription
     *
     * @param int $id
     * @return SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getSubscription(int $id): SubscriptionInterface
    {
        return $this->subscriptionRepository->getById((int) $id);
    }

    /**
     * Get shipping
     *
     * @param int $subscriptionId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
        $this->quoteResource->save($quote);

        $address = $quote->getShippingAddress();
        $rates = $address->collectShippingRates();

        return $rates->getAllShippingRates();
    }

    /**
     * Format price
     *
     * @param float $price
     * @return float|string
     */
    public function formatPrice(float $price)
    {
        return $this->pricingHelper->currency($price);
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
