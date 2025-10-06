<?php

namespace PayPal\Subscription\Plugin;

use Magento\Framework\Registry;
use Magento\InstantPurchase\Block\Button;

class DisableInstantButton
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }
    /**
     * Disable instant button for subscription product
     *
     * @param Button $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsEnabled(Button $subject, bool $result): bool
    {
        $product = $this->registry->registry('current_product');
        if ($product->getSubscriptionAvailable()) {

            return false;
        }

        return $result;
    }
}
