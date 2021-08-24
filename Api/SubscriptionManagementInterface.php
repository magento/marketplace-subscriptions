<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * @api
 */
interface SubscriptionManagementInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param int $frequency
     * @param int|null $frequencyProfileId
     * @return mixed
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createSubscriptionWithItem(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\OrderItemInterface $item,
        int $frequency,
        $frequencyProfileId = null
    );

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param int $frequency
     * @param int|null $frequencyProfileId
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createSubscription(
        \Magento\Sales\Api\Data\OrderInterface $order,
        int $frequency,
        $frequencyProfileId = null
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;

    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $frequency
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     */
    public function changeFrequency(
        int $customerId,
        int $subscriptionId,
        int $frequency
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;

    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $status
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     */
    public function changeStatus(
        int $customerId,
        int $subscriptionId,
        int $status
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;


    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @param int $failedAttempts
     * @return Data\SubscriptionInterface
     */
    public function updateCoultOfFailedAttempts(
        int $customerId,
        int $subscriptionId,
        int $failedAttempts
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;

    /**
     * @param int $customerId
     * @param string $addressType
     * @param int $subscriptionId
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function changeAddressExisting(
        int $customerId,
        string $addressType,
        int $subscriptionId,
        int $addressId
    ): \Magento\Customer\Api\Data\AddressInterface;

    /**
     * @param int $customerId
     * @param string $addressType
     * @param int $subscriptionId
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function changeAddressNew(
        int $customerId,
        string $addressType,
        int $subscriptionId,
        \Magento\Customer\Api\Data\AddressInterface $address
    ): \Magento\Customer\Api\Data\AddressInterface;

    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @param string $paymentPublicHash
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface
     */
    public function changePaymentMethod(
        int $customerId,
        int $subscriptionId,
        string $paymentPublicHash
    ): \PayPal\Subscription\Api\Data\SubscriptionInterface;

    /**
     * @param string $from
     * @param string $to
     * @return \PayPal\Subscription\Api\Data\SubscriptionInterface[]
     */
    public function collectReleases(string $from, string $to): array;
}
