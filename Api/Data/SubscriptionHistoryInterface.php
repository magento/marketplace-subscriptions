<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * Interface SubscriptionHistoryInterface
 */
interface SubscriptionHistoryInterface
{
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const ACTION = 'action';
    public const ACTION_TYPE = 'action_type';
    public const DESCRIPTION = 'description';
    public const ADDITIONAL_DATA = 'additional_data';
    public const ADMIN_ID = 'admin_user_id';
    public const NOTIFIED = 'customer_notified';
    public const VISIBLE = 'visible_to_customer';
    public const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * @param int $id
     * @return SubscriptionHistoryInterface
     */
    public function setSubscriptionId(int $id): SubscriptionHistoryInterface;

    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @param string $action
     * @return SubscriptionHistoryInterface
     */
    public function setAction(string $action): SubscriptionHistoryInterface;

    /**
     * @return string
     */
    public function getActionType(): string;

    /**
     * @param string $actionType
     * @return SubscriptionHistoryInterface
     */
    public function setActionType(string $actionType): SubscriptionHistoryInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return SubscriptionHistoryInterface
     */
    public function setDescription(string $description): SubscriptionHistoryInterface;

    /**
     * @return int
     */
    public function getAdminId(): int;

    /**
     * @param int $adminId
     * @return SubscriptionHistoryInterface
     */
    public function setAdminId(int $adminId): SubscriptionHistoryInterface;

    /**
     * @return bool
     */
    public function getCustomerNotified(): bool;

    /**
     * @param int $customerNotified
     * @return SubscriptionHistoryInterface
     */
    public function setCustomerNotified(int $customerNotified): SubscriptionHistoryInterface;

    /**
     * @return bool
     */
    public function getVisibleToCustomer(): bool;

    /**
     * @param int $visible
     * @return SubscriptionHistoryInterface
     */
    public function setVisibleToCustomer(int $visible): SubscriptionHistoryInterface;
}
