<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\User\Api\Data\UserInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

interface SubscriptionHistoryManagementInterface
{
    /**
     * @param UserInterface $customer
     * @param SubscriptionInterface $subscription
     * @param string $action
     * @return SubscriptionHistoryInterface
     */
    public function recordCustomerHistory(
        UserInterface $customer,
        SubscriptionInterface $subscription,
        string $action
    ): SubscriptionHistoryInterface;

    /**
     * @param UserInterface $admin
     * @param SubscriptionInterface $subscription
     * @param string $action
     * @param int $customerNotified
     * @param int $isVisibleToCustomer
     * @return SubscriptionHistoryInterface
     */
    public function recordAdminHistory(
        UserInterface $admin,
        SubscriptionInterface $subscription,
        string $action,
        int $customerNotified,
        int $isVisibleToCustomer
    ): SubscriptionHistoryInterface;
}
