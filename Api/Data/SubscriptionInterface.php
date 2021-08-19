<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * Interface SubscriptionInterface
 */
interface SubscriptionInterface
{
    public const SUBSCRIPTION_ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const ORDER_ID = 'original_order_id';
    public const STATUS = 'status';
    public const RELEASE_COUNT = 'release_count';
    public const PREV_RELEASE_DATE = 'previous_release_date';
    public const NEXT_RELEASE_DATE = 'next_release_date';
    public const FREQ_PROFILE_ID = 'frequency_profile_id';
    public const FREQUENCY = 'frequency';
    public const BILLING_ADDRESS = 'billing_address';
    public const SHIPPING_ADDRESS = 'shipping_address';
    public const SHIPPING_PRICE = 'shipping_price';
    public const SHIPPING_METHOD = 'shipping_method';
    public const PAYMENT_METHOD = 'payment_method';
    public const PAYMENT_DATA = 'payment_data';
    public const COUNT_OF_FAILED_ATTEMPTS = 'count_of_failed_attempts';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const STATUS_ACTIVE = 1;
    public const STATUS_PAUSED = 2;
    public const STATUS_CANCELLED = 3;
    public const STATUS_EXPIRED = 4;

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param $id
     * @return SubscriptionInterface
     */
    public function setId($id): SubscriptionInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return SubscriptionInterface
     */
    public function setCustomerId(int $customerId): SubscriptionInterface;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return SubscriptionInterface
     */
    public function setOrderId(int $orderId): SubscriptionInterface;

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @param int $statusId
     * @return SubscriptionInterface
     */
    public function setStatus(int $statusId): SubscriptionInterface;

    /**
     * @return string|null
     */
    public function getPreviousReleaseDate(): ?string;

    /**
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setPreviousReleaseDate(string $releaseDate): SubscriptionInterface;

    /**
     * @return string
     */
    public function getNextReleaseDate(): string;

    /**
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setNextReleaseDate(string $releaseDate): SubscriptionInterface;

    /**
     * @return int|null
     */
    public function getFrequencyProfileId(): ?int;

    /**
     * @param int $frequencyProfileId
     * @return SubscriptionInterface
     */
    public function setFrequencyProfileId(int $frequencyProfileId): SubscriptionInterface;

    /**
     * @return int
     */
    public function getFrequency(): int;

    /**
     * @param int $frequency
     * @return SubscriptionInterface
     */
    public function setFrequency(int $frequency): SubscriptionInterface;

    /**
     * @return string
     */
    public function getBillingAddress(): string;

    /**
     * @param string $billingAddress
     * @return SubscriptionInterface
     */
    public function setBillingAddress(string $billingAddress): SubscriptionInterface;

    /**
     * @return string
     */
    public function getShippingAddress(): string;

    /**
     * @param string $shippingAddress
     * @return SubscriptionInterface
     */
    public function setShippingAddress(string $shippingAddress): SubscriptionInterface;

    /**
     * @return float
     */
    public function getShippingPrice(): float;

    /**
     * @param float $shippingPrice
     * @return SubscriptionInterface
     */
    public function setShippingPrice(float $shippingPrice): SubscriptionInterface;

    /**
     * @return string
     */
    public function getShippingMethod(): string;

    /**
     * @param string $shippingMethod
     * @return SubscriptionInterface
     */
    public function setShippingMethod(string $shippingMethod): SubscriptionInterface;

    /**
     * @return string
     */
    public function getPaymentMethod(): string;

    /**
     * @param string $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod(string $paymentMethod): SubscriptionInterface;

    /**
     * @return string|null
     */
    public function getPaymentData(): ?string;

    /**
     * @param string $paymentData
     * @return SubscriptionInterface
     */
    public function setPaymentData(string $paymentData): SubscriptionInterface;

    /**
     * @return int
     */
    public function getCountOfFailedAttempts(): int;

    /**
     * @param int $countOfFailedAttempts
     * @return SubscriptionInterface
     */
    public function setCountOfFailedAttempts(int $countOfFailedAttempts): SubscriptionInterface;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string $createdAt
     * @return SubscriptionInterface
     */
    public function setCreatedAt(string $createdAt): SubscriptionInterface;

    /**
     * @param $action
     * @param $actionType
     * @param $description
     * @param bool $isVisibleToCustomer
     * @param bool $customerNotified
     * @return SubscriptionHistoryInterface
     */
    public function addHistory(
        $action,
        $actionType,
        $description,
        $isVisibleToCustomer = true,
        $customerNotified = true
    ): SubscriptionHistoryInterface;
}
