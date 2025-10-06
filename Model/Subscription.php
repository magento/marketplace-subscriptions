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
    /**
     * @var string
     */
    public $_eventObject = 'subscription';

    /**
     * @var string
     */
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
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionItemCollectionFactory $subscriptionItemCollection,
        ProductRepositoryInterface $productRepository,
        SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory,
        SubscriptionHistoryRepositoryInterface $subscriptionHistoryRepository,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->subscriptionItemCollection = $subscriptionItemCollection;
        $this->productRepository = $productRepository;
        $this->subscriptionHistoryFactory = $subscriptionHistoryFactory;
        $this->subscriptionHistoryRepository = $subscriptionHistoryRepository;
    }

    /**
     * Initialize subscription resource
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(SubscriptionResource::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::SUBSCRIPTION_ID) ?
            (int) $this->getData(self::SUBSCRIPTION_ID) :
            null;
    }

    /**
     * Set ID
     *
     * @param mixed $id
     * @return SubscriptionInterface
     */
    public function setId(mixed $id): SubscriptionInterface
    {
        return $this->setData(
            self::SUBSCRIPTION_ID,
            $id
        );
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return SubscriptionInterface
     */
    public function setCustomerId(int $customerId): SubscriptionInterface
    {
        return $this->setData(
            self::CUSTOMER_ID,
            $customerId
        );
    }

    /**
     * Get order ID
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * Set order ID
     *
     * @param int $orderId
     * @return SubscriptionInterface
     */
    public function setOrderId(int $orderId): SubscriptionInterface
    {
        return $this->setData(
            self::ORDER_ID,
            $orderId
        );
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $statusId
     * @return SubscriptionInterface
     */
    public function setStatus(int $statusId): SubscriptionInterface
    {
        return $this->setData(
            self::STATUS,
            $statusId
        );
    }

    /**
     * Get previous release date
     *
     * @return string|null
     */
    public function getPreviousReleaseDate(): ?string
    {
        return $this->getData(self::PREV_RELEASE_DATE);
    }

    /**
     * Set previous release date
     *
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setPreviousReleaseDate(string $releaseDate): SubscriptionInterface
    {
        return $this->setData(
            self::PREV_RELEASE_DATE,
            $releaseDate
        );
    }

    /**
     * Get next release date
     *
     * @return string
     */
    public function getNextReleaseDate(): string
    {
        return $this->getData(self::NEXT_RELEASE_DATE);
    }

    /**
     * Set next release date
     *
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setNextReleaseDate(string $releaseDate): SubscriptionInterface
    {
        return $this->setData(
            self::NEXT_RELEASE_DATE,
            $releaseDate
        );
    }

    /**
     * Get frequency profile ID
     *
     * @return int|null
     */
    public function getFrequencyProfileId(): ?int
    {
        return $this->getData(self::FREQ_PROFILE_ID) ?
            (int) $this->getData(self::FREQ_PROFILE_ID) :
            null;
    }

    /**
     * Set frequency profile ID
     *
     * @param int|null $frequencyProfileId
     * @return SubscriptionInterface
     */
    public function setFrequencyProfileId(?int $frequencyProfileId): SubscriptionInterface
    {
        return $this->setData(
            self::FREQ_PROFILE_ID,
            $frequencyProfileId
        );
    }

    /**
     * Get frequency
     *
     * @return int
     */
    public function getFrequency(): int
    {
        return (int) $this->getData(self::FREQUENCY);
    }

    /**
     * Set frequency
     *
     * @param int $frequency
     * @return SubscriptionInterface
     */
    public function setFrequency(int $frequency): SubscriptionInterface
    {
        return $this->setData(
            self::FREQUENCY,
            $frequency
        );
    }

    /**
     * Get billing address
     *
     * @return string
     */
    public function getBillingAddress(): string
    {
        return $this->getData(self::BILLING_ADDRESS);
    }

    /**
     * Set billing address
     *
     * @param string $billingAddress
     * @return SubscriptionInterface
     */
    public function setBillingAddress(string $billingAddress): SubscriptionInterface
    {
        return $this->setData(
            self::BILLING_ADDRESS,
            $billingAddress
        );
    }

    /**
     * Get shipping address
     *
     * @return string
     */
    public function getShippingAddress(): string
    {
        return $this->getData(self::SHIPPING_ADDRESS);
    }

    /**
     * Set shipping address
     *
     * @param string $shippingAddress
     * @return SubscriptionInterface
     */
    public function setShippingAddress(string $shippingAddress): SubscriptionInterface
    {
        return $this->setData(
            self::SHIPPING_ADDRESS,
            $shippingAddress
        );
    }

    /**
     * Get shipping price
     *
     * @return float
     */
    public function getShippingPrice(): float
    {
        return (float) $this->getData(self::SHIPPING_PRICE);
    }

    /**
     * Set shipping price
     *
     * @param float $shippingPrice
     * @return SubscriptionInterface
     */
    public function setShippingPrice(float $shippingPrice): SubscriptionInterface
    {
        return $this->setData(
            self::SHIPPING_PRICE,
            $shippingPrice
        );
    }

    /**
     * Get shipping method
     *
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * Set shipping method
     *
     * @param string|null $shippingMethod
     * @return SubscriptionInterface
     */
    public function setShippingMethod($shippingMethod): SubscriptionInterface
    {
        return $this->setData(
            self::SHIPPING_METHOD,
            $shippingMethod
        );
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * Set payment method
     *
     * @param string $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod(string $paymentMethod): SubscriptionInterface
    {
        return $this->setData(
            self::PAYMENT_METHOD,
            $paymentMethod
        );
    }

    /**
     * Get payment data
     *
     * @return string|null
     */
    public function getPaymentData(): ?string
    {
        return $this->getData(self::PAYMENT_DATA);
    }

    /**
     * Set payment data
     *
     * @param string $paymentData
     * @return SubscriptionInterface
     */
    public function setPaymentData(string $paymentData): SubscriptionInterface
    {
        return $this->setData(
            self::PAYMENT_DATA,
            $paymentData
        );
    }

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData('created_at');
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return SubscriptionInterface
     */
    public function setCreatedAt(string $createdAt): SubscriptionInterface
    {
        return $this->setData(
            self::CREATED_AT,
            $createdAt
        );
    }

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return SubscriptionInterface
     */
    public function setUpdatedAt(string $updatedAt): SubscriptionInterface
    {
        return $this->setData(
            self::UPDATED_AT,
            $updatedAt
        );
    }

    /**
     * Get subscription item
     *
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
     * Get product
     *
     * @return ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
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
            return null;
        }
    }

    /**
     * Add subscription history
     *
     * @param string $action
     * @param string $actionType
     * @param string $description
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

    /**
     * Get failed payments
     *
     * @return int
     */
    public function getFailedPayments(): int
    {
        return (int) $this->getData(self::FAILED_PAYMENTS);
    }

    /**
     * Set failed payments
     *
     * @param int $failedPayments
     * @return SubscriptionInterface
     */
    public function setFailedPayments(int $failedPayments): SubscriptionInterface
    {
        return $this->setData(
            self::FAILED_PAYMENTS,
            $failedPayments
        );
    }

    /**
     * Get stock failures
     *
     * @return int
     */
    public function getStockFailures(): int
    {
        return (int) $this->getData(self::STOCK_FAILURES);
    }

    /**
     * Set stock failures
     *
     * @param int $stockFailures
     * @return SubscriptionInterface
     */
    public function setStockFailures(int $stockFailures): SubscriptionInterface
    {
        return $this->setData(
            self::STOCK_FAILURES,
            $stockFailures
        );
    }

    /**
     * Get reminder email sent
     *
     * @return bool
     */
    public function getReminderEmailSent(): bool
    {
        return (bool) $this->getData(self::REMINDER_EMAIL_SENT);
    }

    /**
     * Set reminder email sent
     *
     * @param bool $emailSent
     * @return SubscriptionInterface
     */
    public function setReminderEmailSent(bool $emailSent): SubscriptionInterface
    {
        return $this->setData(
            self::REMINDER_EMAIL_SENT,
            $emailSent
        );
    }
}
