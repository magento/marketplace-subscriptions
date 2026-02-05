<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Subscription;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

interface QuoteAddressResolverInterface
{
    /**
     * Return Quote Address Object from Subscription Address string
     *
     * @param SubscriptionInterface $subscription
     * @param string $addressType
     * @return AddressInterface
     * @throws InputException
     */
    public function execute(
        SubscriptionInterface $subscription,
        string $addressType = SubscriptionInterface::BILLING_ADDRESS
    ): AddressInterface;
}
