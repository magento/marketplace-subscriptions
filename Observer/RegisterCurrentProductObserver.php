<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Registry\CurrentProduct;

class RegisterCurrentProductObserver implements ObserverInterface
{
    /**
     * @var CurrentProduct
     */
    private $currentProduct;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * RegisterCurrentProductObserver constructor.
     *
     * @param CurrentProduct $currentProduct
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        CurrentProduct $currentProduct,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->currentProduct = $currentProduct;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->subscriptionHelper->isActive()) {
            return;
        }

        /** @var ProductInterface $product */
        $product = $observer->getData('product');
        $this->currentProduct->set($product);
    }
}
