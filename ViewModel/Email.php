<?php

declare(strict_types=1);

namespace PayPal\Subscription\ViewModel;

use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Email implements ArgumentInterface
{
    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * Email constructor.
     *
     * @param PricingHelper $pricingHelper
     */
    public function __construct(PricingHelper $pricingHelper)
    {
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return $this->pricingHelper->currency($price, true, false);
    }
}
