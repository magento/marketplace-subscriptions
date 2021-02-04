<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer\Addresses;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Serialize\SerializerInterface;

class UpdateAddress extends Template
{

    /**
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Http
     */
    protected $request;

    /**
     * UpdateAddress constructor.
     * @param Context $context
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CurrentCustomer $currentCustomer
     * @param CountryFactory $countryFactory
     * @param SerializerInterface $serializer
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressCollectionFactory $addressCollectionFactory,
        CurrentCustomer $currentCustomer,
        CountryFactory $countryFactory,
        SerializerInterface $serializer,
        Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->currentCustomer = $currentCustomer;
        $this->countryFactory = $countryFactory;
        $this->serializer = $serializer;
        $this->request = $request;
    }

    /**
     * Get Subscription id
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->request->getParam('id');
    }

    /**
     * Get Customer
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface
    {
        $customer = $this->getData('customer');
        if (!$customer) {
            $customer = $this->currentCustomer->getCustomer();
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * Get all possible addresses
     * @return array
     */
    public function getAllAddresses(): array
    {

        $additional = [];

        $collection = $this->addressCollectionFactory->create();

        $collection->setOrder('entity_id', 'desc');
        $collection->setCustomerFilter([$this->getCustomer()->getId()]);

        foreach ($collection as $address) {

            $address = $address->getDataModel();

            $additional[] = [
                'id' => $address->getId(),
                'subscriptionId' => $this->getSubscriptionId(),
                'address' => [
                    $address->getFirstname() . ' ' . $address->getLastname(),
                    $this->getStreetAddress($address),
                    $address->getCity(),
                    $this->getCountryByCode($address->getCountryId()),
                    $address->getRegion()->getRegion(),
                    $address->getPostcode()
                ],
                'telephone' => $address->getTelephone()
            ];
        }

        return $additional;
    }

    /**
     * Return JSON for knockout
     * @return string
     */
    public function getAllAddressesJson(): string
    {

        return $this->serializer->serialize($this->getAllAddresses());
    }

    /**
     * Get one string street address from the Address DTO passed in parameters
     * @param AddressInterface $address
     * @return string
     */
    public function getStreetAddress(AddressInterface $address): string
    {
        $street = $address->getStreet();
        if (is_array($street)) {
            $street = implode(', ', $street);
        }
        return $street;
    }

    /**
     * Get country name by $countryCode
     * Using \Magento\Directory\Model\Country to get country name by $countryCode
     * @param string $countryCode
     */
    public function getCountryByCode(string $countryCode): string
    {
        /** @var Country $country */
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryCode)->getName();
    }
}
