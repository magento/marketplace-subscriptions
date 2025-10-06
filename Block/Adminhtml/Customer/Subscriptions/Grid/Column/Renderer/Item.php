<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Customer\Subscriptions\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Model\Subscription;
use PayPal\Subscription\Model\SubscriptionItem;

class Item extends Text
{
    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Item constructor
     *
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->pricingHelper = $pricingHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * Return product data for Grid from JSON string
     *
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _getValue(
        DataObject $row
    ): string {
        /** @var SubscriptionInterface|Subscription $subcription */
        $subcription = $row;
        /** @var SubscriptionItemInterface|SubscriptionItem $subscriptionItem */
        $subscriptionItem = $subcription->getSubscriptionItem();
        /** @var ProductInterface $product */
        $product = $this->productRepository->getById(
            (int) $subscriptionItem->getProductId()
        );
        $formattedPrice = $this->pricingHelper->currency($subscriptionItem->getPrice());
        return $product->getName() . ' </br> '
            . $formattedPrice . ' </br> '
            . 'x' . $subscriptionItem->getQty();
    }
}
