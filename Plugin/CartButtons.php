<?php

namespace PayPal\Subscription\Plugin;

use Magento\Checkout\Model\Cart;
use PayPal\Braintree\Block\Paypal\Button;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class CartButtons
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
     * Is cart buttons active
     *
     * @param Button $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(Button $subject, bool $result): bool
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
