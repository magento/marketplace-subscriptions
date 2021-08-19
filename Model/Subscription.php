<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionHistoryRepositoryInterface;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;

class Subscription extends AbstractModel implements SubscriptionInterface
{
    public $_eventObject = 'subscription';

    public $_eventPrefix = 'paypal_subscription';

    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SubscriptionHistoryInterfaceFactory
     */
    private $subscriptionHistoryFactory;

    /**
     * @var SubscriptionHistoryRepositoryInterface
     */
    private $subscriptionHistoryRepository;

    /**
     * Subscription constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollection
     * @param ProductRepositoryInterface $productRepository
     * @param SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory
     * @param SubscriptionHistoryRepositoryInterface $subscriptionHistoryRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionItemCollectionFactory $subscriptionItemCollection,
        ProductRepositoryInterface $productRepository,
        SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory,
        SubscriptionHistoryRepositoryInterface $subscriptionHistoryRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->subscriptionItemCollection = $subscriptionItemCollection;
        $this->productRepository = $productRepository;
        $this->subscriptionHistoryFactory = $subscriptionHistoryFactory;
        $this->subscriptionHistoryRepository = $subscriptionHistoryRepository;
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(SubscriptionResource::class);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::SUBSCRIPTION_ID) ? (int) $this->getData(self::SUBSCRIPTION_ID) : null;
    }

    /**
     * @param mixed $id
     * @return SubscriptionInterface
     */
    public function setId($id): SubscriptionInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $id);
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return SubscriptionInterface
     */
    public function setCustomerId(int $customerId): SubscriptionInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return SubscriptionInterface
     */
    public function setOrderId(int $orderId): SubscriptionInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * @param int $statusId
     * @return SubscriptionInterface
     */
    public function setStatus(int $statusId): SubscriptionInterface
    {
        return $this->setData(self::STATUS, $statusId);
    }

    /**
     * @return string|null
     */
    public function getPreviousReleaseDate(): ?string
    {
        return $this->getData(self::PREV_RELEASE_DATE);
    }

    /**
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setPreviousReleaseDate(string $releaseDate): SubscriptionInterface
    {
        return $this->setData(self::PREV_RELEASE_DATE, $releaseDate);
    }

    /**
     * @return string
     */
    public function getNextReleaseDate(): string
    {
        return $this->getData(self::NEXT_RELEASE_DATE);
    }

    /**
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setNextReleaseDate(string $releaseDate): SubscriptionInterface
    {
        return $this->setData(self::NEXT_RELEASE_DATE, $releaseDate);
    }

    /**
     * @return int|null
     */
    public function getFrequencyProfileId(): ?int
    {
        return $this->getData(self::FREQ_PROFILE_ID) ? (int) $this->getData(self::FREQ_PROFILE_ID) : null;
    }

    /**
     * @param int|null $frequencyProfileId
     * @return SubscriptionInterface
     */
    public function setFrequencyProfileId(?int $frequencyProfileId): SubscriptionInterface
    {
        return $this->setData(self::FREQ_PROFILE_ID, $frequencyProfileId);
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return (int) $this->getData(self::FREQUENCY);
    }

    /**
     * @param int $frequency
     * @return SubscriptionInterface
     */
    public function setFrequency(int $frequency): SubscriptionInterface
    {
        return $this->setData(self::FREQUENCY, $frequency);
    }

    /**
     * @return string
     */
    public function getBillingAddress(): string
    {
        return $this->getData(self::BILLING_ADDRESS);
    }

    /**
     * @param string $billingAddress
     * @return SubscriptionInterface
     */
    public function setBillingAddress(string $billingAddress): SubscriptionInterface
    {
        return $this->setData(self::BILLING_ADDRESS, $billingAddress);
    }

    /**
     * @return string
     */
    public function getShippingAddress(): string
    {
        return $this->getData(self::SHIPPING_ADDRESS);
    }

    /**
     * @param string $shippingAddress
     * @return SubscriptionInterface
     */
    public function setShippingAddress(string $shippingAddress): SubscriptionInterface
    {
        return $this->setData(self::SHIPPING_ADDRESS, $shippingAddress);
    }

    /**
     * @return float
     */
    public function getShippingPrice(): float
    {
        return (float) $this->getData(self::SHIPPING_PRICE);
    }

    /**
     * @param float $shippingPrice
     * @return SubscriptionInterface
     */
    public function setShippingPrice(float $shippingPrice): SubscriptionInterface
    {
        return $this->setData(self::SHIPPING_PRICE, $shippingPrice);
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * @param string $shippingMethod
     * @return SubscriptionInterface
     */
    public function setShippingMethod(string $shippingMethod): SubscriptionInterface
    {
        return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * @param string $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod(string $paymentMethod): SubscriptionInterface
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * @return string|null
     */
    public function getPaymentData(): ?string
    {
        return $this->getData(self::PAYMENT_DATA);
    }

    /**
     * @param string $paymentData
     * @return SubscriptionInterface
     */
    public function setPaymentData(string $paymentData): SubscriptionInterface
    {
        return $this->setData(self::PAYMENT_DATA, $paymentData);
    }

    /**
     * @return int
     */
    public function getCountOfFailedAttempts(): int
    {
        return (int) $this->getData(self::COUNT_OF_FAILED_ATTEMPTS);
    }

    /**
     * @param int $countOfFailedAttempts
     * @return SubscriptionInterface
     */
    public function setCountOfFailedAttempts(int $countOfFailedAttempts): SubscriptionInterface
    {
        return $this->setData(self::COUNT_OF_FAILED_ATTEMPTS, $countOfFailedAttempts);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData('created_at');
    }

    /**
     * @param string $createdAt
     * @return SubscriptionInterface
     */
    public function setCreatedAt(string $createdAt): SubscriptionInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return SubscriptionItemInterface
     */
    public function getSubscriptionItem(): SubscriptionItemInterface
    {
        $subscriptionId = $this->getId();
        $subscriptionItemCollection = $this->subscriptionItemCollection->create();
        $subscriptionItemCollection->addFieldToFilter('subscription_id', ['eq' => $subscriptionId]);
        /** @var SubscriptionItemInterface $subscriptionItem */
        $subscriptionItem = $subscriptionItemCollection->getFirstItem();
        return $subscriptionItem;
    }

    /**
     * @return ProductInterface|void
     */
    public function getProduct()
    {
        $subscriptionId = $this->getId();
        $subscriptionItemCollection = $this->subscriptionItemCollection->create();
        $subscriptionItemCollection->addFieldToFilter('subscription_id', ['eq' => $subscriptionId]);
        /** @var SubscriptionItemInterface $subscriptionItem */
        $subscriptionItem = $subscriptionItemCollection->getFirstItem();

        $productId = $subscriptionItem->getProductId();
        try {
            return $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }

    /**
     * @param $action
     * @param $actionType
     * @param $description
     * @param bool $isVisibleToCustomer
     * @param bool $customerNotified
     * @return SubscriptionHistoryInterface
     * @throws LocalizedException
     */
    public function addHistory(
        $action,
        $actionType,
        $description,
        $isVisibleToCustomer = true,
        $customerNotified = true
    ): SubscriptionHistoryInterface {
        /** @var SubscriptionHistory $history */
        $history = $this->subscriptionHistoryFactory->create();
        $history->setSubscriptionId((int) $this->getId());
        $history->setAction($action);
        $history->setActionType($actionType);
        $history->setDescription($description);
        $history->setVisibleToCustomer((int) $isVisibleToCustomer);
        $history->setCustomerNotified((int) $customerNotified);

        try {
            $this->subscriptionHistoryRepository->save($history);
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__('Could not record subscription history. %1', $e->getMessage()));
        }

        return $history;
    }
}
