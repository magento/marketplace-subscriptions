<?php
declare(strict_types=1);

namespace PayPal\Subscription\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\Subscription;
use PayPal\Subscription\Setup\Patch\Data\AddProductSubscriptionAttributes;

class Totals implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderInterface
     */
    private $originalOrder;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * Totals constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SubscriptionHelper $subscriptionHelper
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SubscriptionHelper $subscriptionHelper,
        PricingHelper $pricingHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Return Subtotal from Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getSubtotal(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int) $subscription->getOrderId()
        );
        $subtotal = $order ? (float) $order->getSubtotalInclTax() : 0.00;
        return $this->pricingHelper->currency($subtotal);
    }

    /**
     * Get saving
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getSaving(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int) $subscription->getOrderId()
        );
        /** @var ProductInterface|Product $subscriptionProduct */
        $subscriptionProduct = $subscription->getProduct();
        if ($subscriptionProduct) {
            $subscriptionPriceType = $subscriptionProduct->getData(
                AddProductSubscriptionAttributes::SUBSCRIPTION_PRICE_TYPE
            );
            $subscriptionProductPriceValue = (float) $subscriptionProduct->getData(
                AddProductSubscriptionAttributes::SUBSCRIPTION_PRICE_VALUE
            );
            $discountedPrice = $subscriptionPriceType == SubscriptionHelper::DISCOUNT_PRICE ?
                $this->subscriptionHelper->getDiscountedPrice(
                    $subscriptionProductPriceValue,
                    (float) $subscriptionProduct->getFinalPrice()
                ) :
                (float) $subscriptionProductPriceValue;

            $discount = (float) $subscriptionProduct->getFinalPrice() - $discountedPrice;
        } else {
            $discount = $order ? (float)$order->getDiscountAmount() : 0.00;
        }
        return $this->pricingHelper->currency($discount);
    }

    /**
     * Return Shipping total from Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getDelivery(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int)$subscription->getOrderId()
        );
        $delivery = $order ? (float) $order->getShippingInclTax() : 0.00;
        return $this->pricingHelper->currency($delivery);
    }

    /**
     * Return Grand total from Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getTotal(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int)$subscription->getOrderId()
        );
        $total = $order ? (float) $order->getGrandTotal() : 0.00;
        return $this->pricingHelper->currency($total);
    }

    /**
     * Return Original Order Object
     *
     * @param int $originalOrderId
     * @return OrderInterface
     */
    private function getOriginalOrder(
        int $originalOrderId
    ): OrderInterface {
        if (!$this->originalOrder ||
            (int) $this->originalOrder->getEntityId() !== $originalOrderId) {
            $this->originalOrder = $this->orderRepository->get($originalOrderId);
        }
        return $this->originalOrder;
    }
}
