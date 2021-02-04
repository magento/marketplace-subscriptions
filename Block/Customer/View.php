<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Theme\Block\Html\Pager;
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
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * @return SubscriptionInterface|void
     */
    public function getSubscription()
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionInterfaceFactory->create();
        $this->subscriptionResource->load($subscription, $this->getSubscriptionId());

        return $subscription;
    }

    /**
     * @param $address
     * @return string
     */
    public function formatAddress($address): string
    {
        $address = $this->serializer->unserialize($address);
        $formattedAddress = new RecursiveIteratorIterator(new RecursiveArrayIterator($address));
        return implode(', ', array_filter(iterator_to_array($formattedAddress, false)));
    }

    /**
     * @param $subscriptionId
     * @return Collection
     */
    public function getSubscriptionReleases($subscriptionId): Collection
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
     * @param $frequencyProfileId
     * @return array
     */
    public function getFrequencyProfileOptions($frequencyProfileId): array
    {
        try {
            $frequencyProfile = $this->frequencyProfile->getById((int) $frequencyProfileId);
            return $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * @param $frequencyProfileId
     * @param $frequency
     * @return string
     */
    public function getFrequencyProfileOptionsJson($frequencyProfileId): string
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
     * @param $price
     * @return string
     */
    public function formatPrice($price): string
    {
        return $this->pricingHelper->currency($price);
    }

    /**
     * @param $product
     * @param array $attributes
     * @return string
     */
    public function getImageUrl($product, $attributes = []): string
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
