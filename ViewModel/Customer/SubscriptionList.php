<?php
declare(strict_types=1);

namespace PayPal\Subscription\ViewModel\Customer;

use Exception;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Block\Customer\Index;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection;
use PayPal\Subscription\Model\ResourceModel\Subscription\CollectionFactory;
use PayPal\Subscription\Setup\Patch\Data\AddProductSubscriptionAttributes;
use PayPal\Subscription\ViewModel\PaymentDetails;
use PayPal\Subscription\ViewModel\PaymentDetailsFactory;
use PayPal\Subscription\ViewModel\TotalsFactory;

class SubscriptionList implements ArgumentInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfileRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderInterface
     */
    private $originalOrder;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CollectionFactory
     */
    private $subscriptionCollectionFactory;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var PaymentDetails
     */
    private $paymentDetailsViewModel;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * SubscriptionList constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param CustomerSession $customerSession
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param SerializerInterface $serializer
     * @param PaymentDetails $paymentDetailsViewModel
     * @param ImageHelper $imageHelper
     * @param TimezoneInterface $localeDate
     * @param PricingHelper $pricingHelper
     * @param SubscriptionHelper $subscriptionHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerSession $customerSession,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        SerializerInterface $serializer,
        PaymentDetails $paymentDetailsViewModel,
        ImageHelper $imageHelper,
        TimezoneInterface $localeDate,
        PricingHelper $pricingHelper,
        SubscriptionHelper $subscriptionHelper,
        OrderRepositoryInterface $orderRepository,
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->customerSession = $customerSession;
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
        $this->subscriptionCollectionFactory = $collectionFactory;
        $this->imageHelper = $imageHelper;
        $this->localeDate = $localeDate;
        $this->pricingHelper = $pricingHelper;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->orderRepository = $orderRepository;
        $this->paymentDetailsViewModel = $paymentDetailsViewModel;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Get array of Customers' active Subscriptions
     *
     * @return SubscriptionInterface[]
     */
    public function getActiveSubscriptions(): array
    {
        $activeSubscriptions = [];
        try {
            $customerSubscriptions = $this->getCustomerSubscriptionsCollection();
            $customerSubscriptions->addFieldToFilter(
                SubscriptionInterface::STATUS,
                SubscriptionInterface::STATUS_ACTIVE
            );
            $activeSubscriptions = $customerSubscriptions->getItems() ?: [];
        } catch (LocalizedException $e) {
            return [];
        }
        return $activeSubscriptions;
    }

    /**
     * Get array of Customers' cancelled Subscriptions
     *
     * @return SubscriptionInterface[]
     */
    public function getCancelledSubscriptions(): array
    {
        $cancelledSubscriptions = [];
        try {
            $customerSubscriptions = $this->getCustomerSubscriptionsCollection();
            $customerSubscriptions->addFieldToFilter(
                SubscriptionInterface::STATUS,
                SubscriptionInterface::STATUS_CANCELLED
            );
            $cancelledSubscriptions = $customerSubscriptions->getItems() ?: [];
        } catch (LocalizedException $e) {
            return [];
        }
        return $cancelledSubscriptions;
    }

    /**
     * Return Current Customers' Subscription Collection
     *
     * @return Collection
     * @throws LocalizedException
     */
    private function getCustomerSubscriptionsCollection(): Collection
    {
        $customerId = $this->getCustomerId();
        if ($customerId === null) {
            throw new LocalizedException(
                __('Unable to determine current Customer ID')
            );
        }
        /** @var Collection $subscriptionCollection */
        $subscriptionCollection = $this->subscriptionCollectionFactory->create();
        $subscriptionCollection->addFieldToFilter(
            SubscriptionInterface::CUSTOMER_ID,
            $customerId
        );
        return $subscriptionCollection;
    }

    /**
     * Return Customer ID
     *
     * @return int|null
     */
    private function getCustomerId(): ?int
    {
        $customerId = $this->customerSession->getCustomerId();
        return $customerId ? (int) $customerId : null;
    }

    /**
     * Return Address HTML string representation
     *
     * @param string $address
     * @return string|Phrase
     */
    public function getAddressHtml(
        string $address
    ): string|Phrase {
        $address = $this->serializer->unserialize($address) ?: [];
        $addressString = '';
        foreach ($address as $key => $data) {
            if ($key === 'region' || $key === 'region_id') {
                continue;
            }
            if ($data != null) {
                if (is_array($data)) {
                    foreach ($data as $k => $addressDatum) {
                        if ($addressDatum != null) {
                            $addressString .= $addressDatum . '</br>';
                        }
                    }
                } else {
                    if ($key === 'firstname') {
                        $addressString .= $data . ' ';
                    } else {
                        $addressString .= $data . '</br>';
                    }
                }
            }
        }
        return $addressString;
    }

    /**
     * Return array of Frequency profile Options for product in Subscription entity
     *
     * @param SubscriptionInterface $subscription
     * @return array
     */
    public function getFrequencyProfileOptions(
        SubscriptionInterface $subscription
    ): array {
        $options = [];
        try {
            /** @var ProductInterface|Product $product */
            $product = $subscription->getProduct();
            $frequencyProfileId = $product->getData(
                'subscription_frequency_profile'
            );
            $frequencyProfile = $this->frequencyProfileRepository->getById(
                (int)$frequencyProfileId
            );
            return $this->serializer->unserialize(
                $frequencyProfile->getFrequencyOptions()
            );
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * Get subscription list json config
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSubscriptionListJsonConfig(): string
    {
        /** @var SubscriptionInterface[] $subscriptions */
        $subscriptions = array_merge_recursive(
            $this->getActiveSubscriptions(),
            $this->getCancelledSubscriptions()
        );
        $subscriptionsData = array_map(
            [$this, 'getSubscriptionData'],
            $subscriptions
        );
        return $this->serializer->serialize([
            'subscriptions' => $subscriptionsData,
            'paymentMethods' => $this->getCustomerPaymentMethods()
        ]);
    }

    /**
     * Return Customers Stored Payment methods
     *
     * @return array
     * @throws LocalizedException
     */
    private function getCustomerPaymentMethods(): array
    {
        $customerId = $this->getCustomerId();
        if ($customerId === null) {
            throw new LocalizedException(
                __('Unable to determine current Customer ID')
            );
        }
        /** @var PaymentTokenInterface[] $customerPaymentTokens */
        $customerPaymentTokens = $this->paymentTokenManagement->getListByCustomerId($customerId);
        $customerPaymentMethods = [];
        foreach ($customerPaymentTokens as $customerPaymentToken) {
            if ($customerPaymentToken->getIsActive() &&
                $customerPaymentToken->getIsVisible()) {
                $customerPaymentMethods[] = [
                    PaymentTokenInterface::CUSTOMER_ID => $customerPaymentToken->getCustomerId(),
                    PaymentTokenInterface::DETAILS => $this->serializer->unserialize(
                        $customerPaymentToken->getTokenDetails()
                    ),
                    PaymentTokenInterface::PUBLIC_HASH => $customerPaymentToken->getPublicHash(),
                    PaymentTokenInterface::TYPE => $customerPaymentToken->getType(),
                    PaymentTokenInterface::PAYMENT_METHOD_CODE => $customerPaymentToken->getPaymentMethodCode()
                ];
            }
        }
        return $customerPaymentMethods;
    }

    /**
     * Get subscription data
     *
     * @param SubscriptionInterface $subscription
     * @return array
     * @throws Exception
     */
    private function getSubscriptionData(
        SubscriptionInterface $subscription
    ): array {
        $subscriptionData = $subscription->getData();
        $product = $subscription->getProduct();
        $subscriptionItem = $subscription->getSubscriptionItem();
        $subscriptionData['product'] = [
            'name' => $product->getName(),
            'image' => $this->getProductImageUrl($product),
            'price' => $this->pricingHelper->currency($subscriptionItem->getPrice()),
            'bundle_options' => $product->getTypeId() === Type::TYPE_CODE ?
                $this->subscriptionHelper->getBundleData($product) :
                []
        ];
        $subscriptionData[SubscriptionInterface::NEXT_RELEASE_DATE] = $this->formatDate(
            $subscription->getNextReleaseDate()
        );
        $subscriptionData[SubscriptionInterface::PREV_RELEASE_DATE] = $subscription->getPreviousReleaseDate() ?
            $this->formatDate(
                $subscription->getPreviousReleaseDate()
            ) :
            null;
        $subscriptionData[SubscriptionInterface::UPDATED_AT] = $subscription->getUpdatedAt() ?
            $this->formatDate(
                $subscription->getUpdatedAt()
            ) :
            null;
        $subscriptionData['frequency_label'] = $this->getFrequencyOptionLabel(
            $subscription,
            $product
        );
        $subscriptionData['available_frequencies'] = $this->getFrequencyProfileOptions($subscription);
        $subscriptionData['item'] = $subscriptionItem->getData();
        $subscriptionData['shipping_address_html'] = $this->getAddressHtml(
            $subscription->getShippingAddress()
        );
        $subscriptionData['totals'] = $this->getTotals($subscription);
        $subscriptionData['payment_details'] = $this->getPaymentDetails($subscription);
        $paymentData = $subscriptionData['payment_data'] ?? '';
        $subscriptionData['payment_data'] = json_decode($paymentData);
        return $subscriptionData;
    }

    /**
     * Return Payment Details array
     *
     * @param SubscriptionInterface $subscription
     * @return array
     */
    private function getPaymentDetails(
        SubscriptionInterface $subscription
    ): array {
        return [
            'masked_card_number' => $this->paymentDetailsViewModel->getMaskedCardNumber($subscription),
            'expiry' => $this->paymentDetailsViewModel->getCardExpiry($subscription),
            'card_type' => $this->paymentDetailsViewModel->getPaymentCardType($subscription),
            'type' => $this->paymentDetailsViewModel->getPaymentType($subscription)
        ];
    }

    /**
     * Return array of totals for a subscription
     *
     * @param SubscriptionInterface $subscription
     * @return array
     */
    private function getTotals(
        SubscriptionInterface $subscription
    ): array {
        return [
            'saving' => $this->getSaving($subscription),
            'subtotal' => $this->getSubtotal($subscription),
            'shipping' => $this->getShipping($subscription),
            'total' => $this->getTotal($subscription)
        ];
    }

    /**
     * Return Subtotal from Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    private function getSubtotal(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int) $subscription->getOrderId()
        );
        $subtotal = $order ? (float) $order->getSubtotalInclTax() : 0.00;
        return $this->pricingHelper->currency($subtotal);
    }

    /**
     * Return Grand total from Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    private function getTotal(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int)$subscription->getOrderId()
        );
        $total = $order ? (float) $order->getGrandTotal() : 0.00;
        return $this->pricingHelper->currency($total);
    }

    /**
     * Return Subscription Saving
     *
     * @param SubscriptionInterface $subscription
     * @return float|string
     */
    private function getSaving(
        SubscriptionInterface $subscription
    ): float|string {
        $order = $this->getOriginalOrder(
            (int) $subscription->getOrderId()
        );
        /** @var ProductInterface|Product $subscriptionProduct */
        $subscriptionProduct = $subscription->getProduct();
        if ($subscriptionProduct) {
            $subscriptionPriceType = $subscriptionProduct->getData(
                AddProductSubscriptionAttributes::SUBSCRIPTION_PRICE_TYPE
            );
            $subscriptionProductPriceValue = (float) $subscriptionProduct->getData(
                AddProductSubscriptionAttributes::SUBSCRIPTION_PRICE_VALUE
            );
            $discountedPrice = $subscriptionPriceType == SubscriptionHelper::DISCOUNT_PRICE ?
                $this->subscriptionHelper->getDiscountedPrice(
                    $subscriptionProductPriceValue,
                    (float) $subscriptionProduct->getFinalPrice()
                ) :
                (float) $subscriptionProductPriceValue;

            $discount = (float) $subscriptionProduct->getFinalPrice() - $discountedPrice;
        } else {
            $discount = $order ? (float)$order->getDiscountAmount() : 0.00;
        }
        return $this->pricingHelper->currency($discount);
    }

    /**
     * Return Frequency Option Label
     *
     * @param SubscriptionInterface $subscription
     * @param ProductInterface $product
     * @return string
     */
    private function getFrequencyOptionLabel(
        SubscriptionInterface $subscription,
        ProductInterface $product
    ): string {
        return $this->subscriptionHelper->getIntervalLabel(
            (int) $product->getId(),
            (int) $subscription->getFrequency()
        );
    }

    /**
     * Return Shipping total from Original Order
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    private function getShipping(
        SubscriptionInterface $subscription
    ): string {
        $order = $this->getOriginalOrder(
            (int)$subscription->getOrderId()
        );
        $delivery = $order ? (float) $order->getShippingInclTax() : 0.00;
        return $this->pricingHelper->currency($delivery);
    }

    /**
     * Return Original Order Object
     *
     * @param int $originalOrderId
     * @return OrderInterface
     */
    private function getOriginalOrder(
        int $originalOrderId
    ): OrderInterface {
        if (!$this->originalOrder ||
            (int) $this->originalOrder->getEntityId() !== $originalOrderId) {
            $this->originalOrder = $this->orderRepository->get($originalOrderId);
        }
        return $this->originalOrder;
    }

    /**
     * Return Formatted Date
     *
     * @param string $date
     * @return string
     * @throws Exception
     */
    private function formatDate(
        string $date
    ): string {
        $dateTime = new \DateTime($date);
        return $this->localeDate->formatDateTime(
            $dateTime,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );
    }

    /**
     * Return Image URL if one exists
     *
     * @param ProductInterface $product
     * @return string|null
     */
    private function getProductImageUrl(
        ProductInterface $product
    ): ?string {
        $imagePath = $product->getImage() ?: null;
        $imageUrl = null;
        if ($imagePath !== null &&
            $imagePath !== 'no_selection') {
            $imageUrl = $this->imageHelper
                ->init(
                    $product,
                    Index::IMAGE_TYPE,
                    []
                )->setImageFile($imagePath)
                ->getUrl();
        }
        return $imageUrl;
    }
}
