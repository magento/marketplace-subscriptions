<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Helper\Data;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection as SubscriptionCollection;
use PayPal\Subscription\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;
use PayPal\Subscription\Model\Subscription;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

/**
 * @api
 * @since 100.0.2
 */
class Index extends Template
{
    public const IMAGE_TYPE = 'paypal_subscription_page';

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * @var SubscriptionCollectionFactory
     */
    private $subscriptionCollectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $subscriptionHelper;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Resolver
     */
    private $locale;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Subscription $subscription
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param Session $customerSession
     * @param Data $subscriptionHelper
     * @param PricingHelper $pricingHelper
     * @param Image $imageHelper
     * @param Resolver $locale
     */
    public function __construct(
        Context $context,
        Subscription $subscription,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        Session $customerSession,
        Data $subscriptionHelper,
        PricingHelper $pricingHelper,
        Image $imageHelper,
        Resolver $locale
    ) {
        parent::__construct($context);

        $this->subscription = $subscription;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->customerSession = $customerSession;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->pricingHelper = $pricingHelper;
        $this->imageHelper = $imageHelper;
        $this->locale = $locale;
    }

    /**
     * Prepare layout for customer subscription
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getCollection()) {
            $pager = $this->getLayout()
                ->createBlock(Pager::class, 'subscription.customer.pager')
                ->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)
                ->setCollection(
                    $this->getCollection()
                );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
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
     * Get subscription collection
     *
     * @return SubscriptionCollection
     */
    public function getCollection()
    {
        $customerId = $this->customerSession->getId();

        $page = $this->getRequest()->getParam('p') ?: 1;
        $pageSize = $this->getRequest()->getParam('limit') ?: 5;

        /** @var SubscriptionCollection $collection */
        $collection = $this->subscriptionCollectionFactory->create();
        $collection->addFieldToFilter(SubscriptionInterface::CUSTOMER_ID, ['eq' => $customerId]);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->setOrder(SubscriptionInterface::CREATED_AT, SortOrder::SORT_DESC);

        return $collection;
    }

    /**
     * Get frequency label
     *
     * @param int $productId
     * @param int $frequency
     * @return string
     */
    public function getFrequencyLabel(int $productId, int $frequency): string
    {
        return $this->subscriptionHelper->getIntervalLabel((int) $productId, $frequency);
    }

    /**
     * Get status label
     *
     * @param int $status
     * @return string
     */
    public function getStatusLabel(int $status): string
    {
        return $this->subscriptionHelper->getStatusLabel($status);
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
        $imagePath = $product->getImage();
        if (!empty($imagePath) && $imagePath !== 'no_selection') {
            $url = $this->imageHelper
                ->init($product, self::IMAGE_TYPE, $attributes)
                ->setImageFile($imagePath)
                ->getUrl();
            return $url;
        }
        return '';
    }

    /**
     * Get Locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        return str_replace('_', '-', $this->locale->getLocale());
    }
}
