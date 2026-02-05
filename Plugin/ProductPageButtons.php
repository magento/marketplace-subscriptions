<?php

namespace PayPal\Subscription\Plugin;

use PayPal\Subscription\Registry\CurrentProduct;
use PayPal\Braintree\Block\Paypal\ProductPage;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class ProductPageButtons
{
    /**
     * @var CurrentProduct
     */
    protected CurrentProduct $currentProduct;

    /**
     * Product constructor
     *
     * @param CurrentProduct $currentProduct
     */
    public function __construct(
        CurrentProduct $currentProduct
    ) {
        $this->currentProduct = $currentProduct;
    }
    /**
     * Is product page buttons active
     *
     * @param ProductPage $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(ProductPage $subject, bool $result): bool
    {
        $currentProduct = $this->currentProduct->get();
        if ($currentProduct->getData(SubscriptionHelper::SUB_AVAILABLE)) {

            return false;
        }

        return $result;
    }
}
