<?php
declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class AddSubscriptionLayoutHandle implements ObserverInterface
{
    private const SUBSCRIPTION_PRODUCT_LAYOUT_HANDLE = 'catalog_product_view_subscription';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * AddSubscriptionLayoutHandle constructor
     *
     * @param LayoutInterface $layout
     * @param SubscriptionHelper $subscriptionHelper
     * @param Registry $registry
     */
    public function __construct(
        LayoutInterface $layout,
        SubscriptionHelper $subscriptionHelper,
        Registry $registry
    ) {
        $this->layout = $layout;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->registry = $registry;
    }

    /**
     * Add Subscription Product page layout handle
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $action = $observer->getData('full_action_name');
        $product = $this->registry->registry('current_product');
        if ($action != 'catalog_product_view' ||
            !$product) {
            return $this;
        }
        if (!$this->subscriptionHelper->isActive()) {
            return;
        }
        $isAvailableAsSubscription = (bool) $product->getData(
            SubscriptionHelper::SUB_AVAILABLE
        );
        if ($isAvailableAsSubscription === true) {
            $layout = $observer->getData('layout');
            $layout->getUpdate()->addHandle(
                self::SUBSCRIPTION_PRODUCT_LAYOUT_HANDLE
            );
        }
    }
}
