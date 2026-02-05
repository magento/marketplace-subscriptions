<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Subscription;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

class QuoteAddressResolver implements QuoteAddressResolverInterface
{
    /**
     * @var AddressInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * QuoteAddressResolver constructor
     *
     * @param AddressInterfaceFactory $quoteAddressFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        AddressInterfaceFactory $quoteAddressFactory,
        SerializerInterface $serializer
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        SubscriptionInterface $subscription,
        string $addressType = SubscriptionInterface::BILLING_ADDRESS
    ): AddressInterface {
        $allowedAddressTypes = [
            SubscriptionInterface::BILLING_ADDRESS,
            SubscriptionInterface::SHIPPING_ADDRESS
        ];
        if (!in_array(
            $addressType,
            $allowedAddressTypes
        )) {
            throw new InputException(
                __('Invalid Address Type for Subscription Quote Address Resolver')
            );
        }
        $subscriptionAddress = $addressType === SubscriptionInterface::BILLING_ADDRESS ?
            $subscription->getBillingAddress() :
            $subscription->getShippingAddress();
        $subscriptionAddressData = $this->serializer->unserialize($subscriptionAddress);
        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->setFirstname($subscriptionAddressData[AddressInterface::KEY_FIRSTNAME])
            ->setLastname($subscriptionAddressData[AddressInterface::KEY_LASTNAME])
            ->setCompany($subscriptionAddressData[AddressInterface::KEY_COMPANY] ?? null)
            ->setStreet($subscriptionAddressData[AddressInterface::KEY_STREET])
            ->setCity($subscriptionAddressData[AddressInterface::KEY_CITY])
            ->setRegion($subscriptionAddressData[AddressInterface::KEY_REGION] ?? null)
            ->setRegionId($subscriptionAddressData[AddressInterface::KEY_REGION_ID] ?? null)
            ->setCountryId($subscriptionAddressData[AddressInterface::KEY_COUNTRY_ID])
            ->setPostcode($subscriptionAddressData[AddressInterface::KEY_POSTCODE] ?? null)
            ->setTelephone($subscriptionAddressData[AddressInterface::KEY_TELEPHONE]);
        return $quoteAddress;
    }
}
