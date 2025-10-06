<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const FAILED_PAYMENTS = 'failed_payments';
    public const STOCK_FAILURES = 'stock_failures';
    public const REMINDER_EMAIL_SENT = 'reminder_email_sent';

    public const STATUS_ACTIVE = 1;
    public const STATUS_PAUSED = 2;
    public const STATUS_CANCELLED = 3;
    public const STATUS_EXPIRED = 4;

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Set id
     *
     * @param mixed $id
     * @return SubscriptionInterface
     */
    public function setId(mixed $id): SubscriptionInterface;

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return SubscriptionInterface
     */
    public function setCustomerId(int $customerId): SubscriptionInterface;

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Set order id
     *
     * @param int $orderId
     * @return SubscriptionInterface
     */
    public function setOrderId(int $orderId): SubscriptionInterface;

    /**
     * Get status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $statusId
     * @return SubscriptionInterface
     */
    public function setStatus(int $statusId): SubscriptionInterface;

    /**
     * Get previous release date
     *
     * @return string|null
     */
    public function getPreviousReleaseDate(): ?string;

    /**
     * Set previous release date
     *
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setPreviousReleaseDate(string $releaseDate): SubscriptionInterface;

    /**
     * Get next release date
     *
     * @return string
     */
    public function getNextReleaseDate(): string;

    /**
     * Set next release date
     *
     * @param string $releaseDate
     * @return SubscriptionInterface
     */
    public function setNextReleaseDate(string $releaseDate): SubscriptionInterface;

    /**
     * Get frequency profile id
     *
     * @return int|null
     */
    public function getFrequencyProfileId(): ?int;

    /**
     * Set frequency profile id
     *
     * @param int $frequencyProfileId
     * @return SubscriptionInterface
     */
    public function setFrequencyProfileId(int $frequencyProfileId): SubscriptionInterface;

    /**
     * Get frequency
     *
     * @return int
     */
    public function getFrequency(): int;

    /**
     * Set frequency
     *
     * @param int $frequency
     * @return SubscriptionInterface
     */
    public function setFrequency(int $frequency): SubscriptionInterface;

    /**
     * Get customer billing address
     *
     * @return string
     */
    public function getBillingAddress(): string;

    /**
     * Set customer billing address
     *
     * @param string $billingAddress
     * @return SubscriptionInterface
     */
    public function setBillingAddress(string $billingAddress): SubscriptionInterface;

    /**
     * Get customer shipping address
     *
     * @return string
     */
    public function getShippingAddress(): string;

    /**
     * Set customer shipping address
     *
     * @param string $shippingAddress
     * @return SubscriptionInterface
     */
    public function setShippingAddress(string $shippingAddress): SubscriptionInterface;

    /**
     * Get shipping price
     *
     * @return float
     */
    public function getShippingPrice(): float;

    /**
     * Set shipping price
     *
     * @param float $shippingPrice
     * @return SubscriptionInterface
     */
    public function setShippingPrice(float $shippingPrice): SubscriptionInterface;

    /**
     * Get shipping method
     *
     * @return string
     */
    public function getShippingMethod(): string;

    /**
     * Set shipping method
     *
     * @param string $shippingMethod
     * @return SubscriptionInterface
     */
    public function setShippingMethod(string $shippingMethod): SubscriptionInterface;

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod(): string;

    /**
     * Set payment method
     *
     * @param string $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod(string $paymentMethod): SubscriptionInterface;

    /**
     * Get payment data
     *
     * @return string|null
     */
    public function getPaymentData(): ?string;

    /**
     * Set payment data
     *
     * @param string $paymentData
     * @return SubscriptionInterface
     */
    public function setPaymentData(string $paymentData): SubscriptionInterface;

    /**
     * Get created date
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created date
     *
     * @param string $createdAt
     * @return SubscriptionInterface
     */
    public function setCreatedAt(string $createdAt): SubscriptionInterface;

    /**
     * Get updated date
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated date
     *
     * @param string $updatedAt
     * @return SubscriptionInterface
     */
    public function setUpdatedAt(string $updatedAt): SubscriptionInterface;

    /**
     * Add history
     *
     * @param string $action
     * @param string $actionType
     * @param string $description
     * @param bool $isVisibleToCustomer
     * @param bool $customerNotified
     * @return SubscriptionHistoryInterface
     */
    public function addHistory(
        string $action,
        string $actionType,
        string $description,
        $isVisibleToCustomer = true,
        $customerNotified = true
    ): SubscriptionHistoryInterface;

    /**
     * Get failed payments
     *
     * @return int|null
     */
    public function getFailedPayments(): int;

    /**
     * Set failed payments
     *
     * @param int $failedPayments
     * @return SubscriptionInterface
     */
    public function setFailedPayments(int $failedPayments): SubscriptionInterface;

    /**
     * Get stock failures
     *
     * @return int|null
     */
    public function getStockFailures(): int;

    /**
     * Set stock failures
     *
     * @param int $stockFailures
     * @return SubscriptionInterface
     */
    public function setStockFailures(int $stockFailures): SubscriptionInterface;

    /**
     * Get reminder email sent flag
     *
     * @return bool
     */
    public function getReminderEmailSent(): bool;

    /**
     * Set reminder email sent flag
     *
     * @param bool $emailSent
     * @return SubscriptionInterface
     */
    public function setReminderEmailSent(bool $emailSent): SubscriptionInterface;
}
