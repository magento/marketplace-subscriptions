<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

interface SubscriptionHistoryManagementInterface
{
    /**
     * Record customer history
     *
     * @param \Magento\User\Api\Data\UserInterface $customer
     * @param \PayPal\Subscription\Api\Data\SubscriptionInterface $subscription
     * @param string $action
     * @return \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface
     */
    public function recordCustomerHistory(
        \Magento\User\Api\Data\UserInterface $customer,
        \PayPal\Subscription\Api\Data\SubscriptionInterface $subscription,
        string $action
    ): \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;

    /**
     * Record admin history
     *
     * @param \Magento\User\Api\Data\UserInterface $admin
     * @param \PayPal\Subscription\Api\Data\SubscriptionInterface $subscription
     * @param string $action
     * @param int $customerNotified
     * @param int $isVisibleToCustomer
     * @return \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface
     */
    public function recordAdminHistory(
        \Magento\User\Api\Data\UserInterface $admin,
        \PayPal\Subscription\Api\Data\SubscriptionInterface $subscription,
        string $action,
        int $customerNotified,
        int $isVisibleToCustomer
    ): \PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
}
