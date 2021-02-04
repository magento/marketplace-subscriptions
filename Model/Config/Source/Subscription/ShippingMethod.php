<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
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
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $data = [
            ['label' => "-- Please Select --", 'value' => '']
        ];

        $methods = $this->getShippingMethods();

        foreach ($methods as $method) {
            $data[] = [
                'label' => sprintf(
                    '%s - %s %s',
                    $method->getCarrierTitle(),
                    $method->getMethodTitle(),
                    $this->subscriptionHelper->formatPrice($method->getPrice())
                ),
                'value' => $method->getCode()
            ];
        }
        return $data;
    }

    protected function getShippingMethods()
    {
        $id = $this->request->getParam('id');
        return $this->subscriptionHelper->getShipping($id);
    }
}
