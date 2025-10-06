<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

/**
 * @api
 */
interface SubscriptionManagementInterface
{
    /**
     * Create subscription with item
     *
     * @param OrderInterface $order
     * @param OrderItemInterface $item
     * @param int $frequency
     * @param int|null $frequencyProfileId
     * @return mixed
     * @throws AlreadyExistsException
     */
    public function createSubscriptionWithItem(
        OrderInterface $order,
        OrderItemInterface $item,
        int $frequency,
        ?int $frequencyProfileId = null
    );

    /**
     * Create subscription
     *
     * @param OrderInterface $order
     * @param int $frequency
     * @param int|null $frequencyProfileId
     * @return SubscriptionInterface
     * @throws AlreadyExistsException
     */
    public function createSubscription(
        OrderInterface $order,
        int $frequency,
        ?int $frequencyProfileId = null
    ): SubscriptionInterface;

    /**
     * Change frequency
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $frequency
     * @return SubscriptionInterface
     */
    public function changeFrequency(
        int $customerId,
        int $subscriptionId,
        int $frequency
    ): SubscriptionInterface;

    /**
     * Change subscription status
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $status
     * @return SubscriptionInterface
     */
    public function changeStatus(
        int $customerId,
        int $subscriptionId,
        int $status
    ): SubscriptionInterface;

    /**
     * Change existing address
     *
     * @param int $customerId
     * @param string $addressType
     * @param int $subscriptionId
     * @param int $addressId
     * @return AddressInterface
     */
    public function changeAddressExisting(
        int $customerId,
        string $addressType,
        int $subscriptionId,
        int $addressId
    ): AddressInterface;

    /**
     * Change new address
     *
     * @param int $customerId
     * @param string $addressType
     * @param int $subscriptionId
     * @param AddressInterface $address
     * @return AddressInterface
     */
    public function changeAddressNew(
        int $customerId,
        string $addressType,
        int $subscriptionId,
        AddressInterface $address
    ): AddressInterface;

    /**
     * Change payment method
     *
     * @param int $customerId
     * @param int $subscriptionId
     * @param string $paymentPublicHash
     * @return SubscriptionInterface
     */
    public function changePaymentMethod(
        int $customerId,
        int $subscriptionId,
        string $paymentPublicHash
    ): SubscriptionInterface;

    /**
     * Collect releases
     *
     * @param string $from
     * @param string $to
     * @param bool|null $emailReminder
     * @return SubscriptionInterface[]
     */
    public function collectReleases(
        string $from,
        string $to,
        ?bool $emailReminder = null
    ): array;
}
