<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Subscriptions\Edit;

use Magento\Backend\Block\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
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
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use PayPal\Subscription\Model\Subscription;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

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
        SerializerInterface $serializer,
        AddressInterfaceFactory $addressFactory,
        PricingHelper $pricingHelper,
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
        $this->serializer = $serializer;
        $this->addressFactory = $addressFactory;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->request->getParam('id');
    }

    /**
     * @param $id
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     * @throws NoSuchEntityException
     */
    public function getSubscription($id)
    {
        return $this->subscriptionRepository->getById((int) $id);
    }

    /**
     * @param $subscriptionId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
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
        $this->quoteResource->save($quote);

        $address = $quote->getShippingAddress();
        $rates = $address->collectShippingRates();

        return $rates->getAllShippingRates();
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->pricingHelper->currency($price);
    }

    /**
     * @param string $address
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
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
