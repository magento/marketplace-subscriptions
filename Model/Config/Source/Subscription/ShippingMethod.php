<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class ShippingMethod implements OptionSourceInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * ShippingMethod constructor.
     *
     * @param RequestInterface $request
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        RequestInterface $request,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->request = $request;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * Shipping method options array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $data = [
            ['label' => "-- Please Select --", 'value' => '']
        ];

        $methods = $this->getShippingMethods();

        foreach ($methods ?? [] as $method) {
            $data[] = [
                'label' => sprintf(
                    '%s - %s %s',
                    $method->getCarrierTitle(),
                    $method->getMethodTitle(),
                    $this->subscriptionHelper->formatPrice((float) $method->getPrice())
                ),
                'value' => $method->getCode()
            ];
        }
        return $data;
    }

    /**
     * Get list of shipping methods
     *
     * @return array
     */
    private function getShippingMethods(): array
    {
        $id = $this->request->getParam('id');
        try {
            return $this->subscriptionHelper->getShipping((int)$id);
        } catch (LocalizedException $e) {
            return [];
        }
    }
}
