<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * Interface SubscriptionReleaseInterface
 */
interface SubscriptionReleaseInterface
{
    public const RELEASE_ID = 'id';
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const CUSTOMER_ID = 'customer_id';
    public const ORDER_ID = 'order_id';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';

    public const STATUS_ACTIVE = 1;
    public const STATUS_PAUSED = 2;
    public const STATUS_CANCELLED = 3;
    public const STATUS_EXPIRED = 4;

    /**
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * @param int $subscriptionId
     * @return SubscriptionReleaseInterface
     */
    public function setSubscriptionId(int $subscriptionId): SubscriptionReleaseInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return SubscriptionReleaseInterface
     */
    public function setCustomerId(int $customerId): SubscriptionReleaseInterface;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return SubscriptionReleaseInterface
     */
    public function setOrderId(int $orderId): SubscriptionReleaseInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return SubscriptionReleaseInterface
     */
    public function setStatus(int $status): SubscriptionReleaseInterface;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;
}
