<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Email;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;
use PayPal\Subscription\Model\Email;

class Release extends Email
{
    private const TEMPLATE_FAILURE = 'paypal_subscriptions_configuration_release_failure';
    private const CONFIG_FAILURE = 'paypal_subscriptions/configuration/release_failure';

    /**
     * @param $subscriptionItem
     * @param CustomerInterface $customer
     * @param $subscription
     * @return array
     */
    public function failure($subscriptionItem, CustomerInterface $customer, $subscription)
    {
        $data = [
            'customer_name' => sprintf('%1$s %2$s', $customer->getFirstname(), $customer->getLastname()),
            'item' => $subscriptionItem,
            'subscription' => $subscription
        ];

        return $this->sendEmail($data, $customer, $this->getCustomTemplate());
    }

    /**
     * @param string $reason
     * @return array
     */
    public function failureAdmin(string $reason)
    {
        $data = [
            'customer_name' => 'Unknown',
            'failure_reason' => $reason,
            'item' => []
        ];

        return $this->sendEmailAdmin($data, $this->getCustomTemplate());
    }

    /**
     * @return string
     */
    private function getCustomTemplate()
    {
        $template = $this->getScopeConfig()->getValue(
            self::CONFIG_FAILURE,
            ScopeInterface::SCOPE_STORE
        );
        return $template ?? self::TEMPLATE_FAILURE;
    }
}
