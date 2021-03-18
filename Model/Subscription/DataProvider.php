<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Subscription;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFactory;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Model\FrequencyProfile;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection;
use PayPal\Subscription\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;
use PayPal\Subscription\Model\Subscription;
use PayPal\Subscription\Ui\Component\Form\Field\Address as AddressFormatter;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfileRepository;

    /**
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PaymentTokenCollectionFactory
     */
    private $paymentTokenCollectionFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var AddressFormatter
     */
    private $addressFormatter;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var []
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param PaymentTokenCollectionFactory $paymentTokenCollectionFactory
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param SerializerInterface $serializer
     * @param AddressFormatter $addressFormatter
     * @param SubscriptionHelper $subscriptionHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        AddressCollectionFactory $addressCollectionFactory,
        PaymentTokenCollectionFactory $paymentTokenCollectionFactory,
        PaymentTokenManagement $paymentTokenManagement,
        SerializerInterface $serializer,
        AddressFormatter $addressFormatter,
        SubscriptionHelper $subscriptionHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $subscriptionCollectionFactory->create();
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->serializer = $serializer;
        $this->addressFormatter = $addressFormatter;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->getCollection();

        foreach ($items as $subscription) {
            /** @var Subscription $subscription */
            $this->loadedData[$subscription->getId()] = $subscription->getData();

            // Fieldset mapping
            $this->loadedData[$subscription->getId()]['overview'] = [
                Subscription::SUBSCRIPTION_ID => $subscription->getId(),
                Subscription::STATUS => $subscription->getStatus(),
                Subscription::PREV_RELEASE_DATE => $subscription->getPreviousReleaseDate(),
                Subscription::NEXT_RELEASE_DATE => $subscription->getNextReleaseDate(),
                Subscription::FREQUENCY => $subscription->getFrequency()
            ];

            $shippingPrice = $this->subscriptionHelper->formatPrice($subscription->getShippingPrice());
            $this->loadedData[$subscription->getId()]['shipping'] = [
                Subscription::BILLING_ADDRESS => $this->addressFormatter->format($subscription->getBillingAddress()),
                Subscription::SHIPPING_ADDRESS => $this->addressFormatter->format($subscription->getShippingAddress()),
                Subscription::SHIPPING_METHOD => $subscription->getShippingMethod() . ' ' . $shippingPrice
            ];

            // Get Frequency Profile
            if ($subscription->getFrequencyProfileId()) {
                /** @var FrequencyProfile $frequencyProfile */
                $frequencyProfile = $this->frequencyProfileRepository->getById($subscription->getFrequencyProfileId());
                $frequencyOptions = $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
                foreach ($frequencyOptions as $option) {
                    $this->loadedData[$subscription->getId()]['frequencyOptions'][] = [
                        'label' => $option['name'],
                        'value' => $option['interval']
                    ];
                }
            }

            // Get customer addresses on file
            $addresses = [];
            /** @var Address $address */
            foreach ($addresses as $address) {
                $addressParts = array_filter([
                    $address->getFirstname() . ' ' . $address->getLastname(),
                    $address->getCompany(),
                    $address->getStreetFull(),
                    $address->getCity(),
                    $address->getRegion(),
                    $address->getPostcode()
                ]);
                $this->loadedData[$subscription->getId()]['addresses'][] = [
                    'label' => implode(', ', $addressParts),
                    'value' => $address->getId()
                ];
            }

            // Set current payment method
            if ($subscription->getPaymentData()) {
                $paymentData = $this->serializer->unserialize($subscription->getPaymentData());
                $currentPaymentMethod = $this->paymentTokenManagement->getByPublicHash(
                    $paymentData['public_hash'],
                    $subscription->getCustomerId()
                );
                $details = $this->serializer->unserialize($currentPaymentMethod->getTokenDetails());

                $this->loadedData[$subscription->getId()]['payment']['payment_method'] =
                    $this->getFriendlyPaymentMethod(
                        $subscription->getPaymentMethod(),
                        $details
                    );
            }

            // Get customer vaulted payment methods
            $paymentMethods = $this->paymentTokenManagement->getVisibleAvailableTokens($subscription->getCustomerId());
            /** @var PaymentToken $paymentMethod */
            foreach ($paymentMethods as $paymentMethod) {
                $details = $this->serializer->unserialize($paymentMethod->getTokenDetails());
                $this->loadedData[$subscription->getId()]['paymentMethods'][] = [
                    'label' => $this->getFriendlyPaymentMethod(
                        $paymentMethod->getPaymentMethodCode(),
                        $details
                    ) ?? $paymentMethod->getPaymentMethodCode(),
                    'value' => $paymentMethod->getPublicHash()
                ];
            }
        }

        return $this->loadedData;
    }

    private function getFriendlyPaymentMethod($method, $details)
    {
        if ($method === 'braintree') {
            $label = sprintf('Credit Card: %s ending %s', $details['type'], $details['maskedCC']);
        } elseif ($method === 'braintree_paypal') {
            $label = sprintf('PayPal: %s', $details['payerEmail']);
        }

        return $label;
    }
}
