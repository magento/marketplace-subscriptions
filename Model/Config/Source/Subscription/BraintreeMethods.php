<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Payment\Model\Config\Source\Allmethods;
use PayPal\Braintree\Model\Ui\ConfigProvider;

class BraintreeMethods extends Allmethods
{
    /**
     * Retrieve Braintree methods
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $paymentList = $this->_paymentData->getPaymentMethodList(true, true, true);
        $groupName = ConfigProvider::CODE . '_group';
        return isset($paymentList[$groupName]) ? [$groupName => $paymentList[$groupName]] : [];
    }
}
