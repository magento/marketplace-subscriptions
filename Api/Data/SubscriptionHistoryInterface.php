<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    public const CHANGE_FREQUENCY_ACTION  = "Change Frequency";
    public const CHANGE_STATUS_ACTION  = "Change Status";
    public const CHANGE_ADDRESS_ACTION  = "Change Address";
    public const ADD_ADDRESS_ACTION  = "Add Address";
    public const CHANGE_PAYMENT_METHOD  = "Change Payment Method";
    public const CHANGE_ITEM_QTY_ACTION  = "Change Item Quantity";

    /**
     * Get subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * Set subscription id
     *
     * @param int $id
     * @return SubscriptionHistoryInterface
     */
    public function setSubscriptionId(int $id): SubscriptionHistoryInterface;

    /**
     * Get action
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Set action
     *
     * @param string $action
     * @return SubscriptionHistoryInterface
     */
    public function setAction(string $action): SubscriptionHistoryInterface;

    /**
     * Get action type
     *
     * @return string
     */
    public function getActionType(): string;

    /**
     * Set action type
     *
     * @param string $actionType
     * @return SubscriptionHistoryInterface
     */
    public function setActionType(string $actionType): SubscriptionHistoryInterface;

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Set description
     *
     * @param string $description
     * @return SubscriptionHistoryInterface
     */
    public function setDescription(string $description): SubscriptionHistoryInterface;

    /**
     * Get admin id
     *
     * @return int
     */
    public function getAdminId(): int;

    /**
     * Set admin id
     *
     * @param int $adminId
     * @return SubscriptionHistoryInterface
     */
    public function setAdminId(int $adminId): SubscriptionHistoryInterface;

    /**
     * Get customer notified
     *
     * @return bool
     */
    public function getCustomerNotified(): bool;

    /**
     * Set customer notified
     *
     * @param int $customerNotified
     * @return SubscriptionHistoryInterface
     */
    public function setCustomerNotified(int $customerNotified): SubscriptionHistoryInterface;

    /**
     * Get visible to customer
     *
     * @return bool
     */
    public function getVisibleToCustomer(): bool;

    /**
     * Set visible to customer
     *
     * @param int $visible
     * @return SubscriptionHistoryInterface
     */
    public function setVisibleToCustomer(int $visible): SubscriptionHistoryInterface;
}
