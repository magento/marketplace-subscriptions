<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionReleaseInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease\Collection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease\CollectionFactory as SubscriptionReleaseCollection;
use PayPal\Subscription\Model\Subscription;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Magento\Catalog\Helper\Image;

/**
 * @api
 * @since 100.0.2
 */
class View extends Template
{
    private const IMAGE_TYPE = 'paypal_subscription_page';

    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscriptionInterfaceFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderInterfaceFactory;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var SubscriptionReleaseCollection
     */
    private $subscriptionReleaseCollectionFactory;

    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfile;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param SubscriptionInterfaceFactory $subscriptionInterfaceFactory
     * @param SubscriptionResource $subscriptionResource
     * @param OrderInterfaceFactory $orderInterfaceFactory
     * @param OrderResource $orderResource
     * @param SubscriptionReleaseCollection $subscriptionReleaseCollectionFactory
     * @param FrequencyProfileRepositoryInterface $frequencyProfile
     * @param SerializerInterface $serializer
     * @param Image $imageHelper
     * @param PricingHelper $pricingHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SubscriptionInterfaceFactory $subscriptionInterfaceFactory,
        SubscriptionResource $subscriptionResource,
        OrderInterfaceFactory $orderInterfaceFactory,
        OrderResource $orderResource,
        SubscriptionReleaseCollection $subscriptionReleaseCollectionFactory,
        FrequencyProfileRepositoryInterface $frequencyProfile,
        SerializerInterface $serializer,
        Image $imageHelper,
        PricingHelper $pricingHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->subscriptionInterfaceFactory = $subscriptionInterfaceFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderResource = $orderResource;
        $this->subscriptionReleaseCollectionFactory = $subscriptionReleaseCollectionFactory;
        $this->frequencyProfile = $frequencyProfile;
        $this->serializer = $serializer;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Get pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * Get subscription
     *
     * @return SubscriptionInterface
     */
    public function getSubscription()
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionInterfaceFactory->create();
        $this->subscriptionResource->load($subscription, $this->getSubscriptionId());

        return $subscription;
    }

    /**
     * Format address
     *
     * @param string $address
     * @return string
     */
    public function formatAddress(string $address): string
    {
        $address = $this->serializer->unserialize($address);
        $formattedAddress = new RecursiveIteratorIterator(new RecursiveArrayIterator($address));
        return implode(', ', array_filter(iterator_to_array($formattedAddress, false)));
    }

    /**
     * Get subscription releases
     *
     * @param int $subscriptionId
     * @return Collection
     */
    public function getSubscriptionReleases(int $subscriptionId): Collection
    {
        $page = $this->getRequest()->getParam('release-page') ?: 1;
        $pageSize = $this->getRequest()->getParam('release-limit') ?: 5;

        /** @var Collection $collection */
        $collection = $this->subscriptionReleaseCollectionFactory->create();
        $collection->addFieldToFilter(SubscriptionReleaseInterface::SUBSCRIPTION_ID, ['eq' => $subscriptionId]);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->setOrder(SubscriptionReleaseInterface::CREATED_AT, SortOrder::SORT_DESC);

        return $collection;
    }

    /**
     * Get frequency profile options
     *
     * @param int $frequencyProfileId
     * @return array
     */
    public function getFrequencyProfileOptions(int $frequencyProfileId): array
    {
        try {
            $frequencyProfile = $this->frequencyProfile->getById((int) $frequencyProfileId);
            return $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * Get frequency profile options json
     *
     * @param int $frequencyProfileId
     * @return string
     */
    public function getFrequencyProfileOptionsJson(int $frequencyProfileId): string
    {
        return $this->serializer->serialize($this->getFrequencyProfileOptions($frequencyProfileId));
    }

    /**
     * Return order view url
     *
     * @param integer $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Get available status
     *
     * @return bool|string
     */
    public function getAvailableStatus()
    {
        $statusArray = [
            1 => 'Active',
            2 => 'Pause',
            3 => 'Cancel'
        ];

        return $this->serializer->serialize($statusArray);
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return $this->pricingHelper->currency($price);
    }

    /**
     * Get image url
     *
     * @param Product $product
     * @param array $attributes
     * @return string
     */
    public function getImageUrl(Product $product, $attributes = []): string
    {
        $imageType = self::IMAGE_TYPE;
        $imagePath = $product->getProductUrl();

        if ($imagePath && $imagePath !== '' && $imagePath !== 'no_selection') {

            // Get Image Url
            $image = $this->imageHelper
                ->init($product, $imageType, $attributes)
                ->setImageFile($imagePath)
                ->getUrl();

            return $image;
        }

        return '';
    }
}
