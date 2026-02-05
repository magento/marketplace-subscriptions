<?php

namespace PayPal\Subscription\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Multishipping\Helper\Data;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class MultishippingSubscription
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * ConfigProvider constructor.
     *
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Is multi shipping checkout available
     *
     * @param Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsMultishippingCheckoutAvailable(Data $subject, bool $result): bool
    {
        $items = $this->cart->getItems();
        foreach ($items as $item) {
            $isSubscriptionOption = $item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION);
            if ($isSubscriptionOption !== null) {
                if ($isSubscriptionOption->getValue() === '1') {
                    return false;
                }
            }
        }

        return $result;
    }
}
