<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\SubscriptionReleaseInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease as SubscriptionReleaseResource;

class SubscriptionRelease extends AbstractModel implements SubscriptionReleaseInterface
{
    protected function _construct()
    {
        $this->_init(SubscriptionReleaseResource::class);
    }

    /**
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * @param int $subscriptionId
     * @return SubscriptionReleaseInterface
     */
    public function setSubscriptionId(int $subscriptionId): SubscriptionReleaseInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return SubscriptionReleaseInterface
     */
    public function setCustomerId(int $customerId): SubscriptionReleaseInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return SubscriptionReleaseInterface
     */
    public function setOrderId(int $orderId): SubscriptionReleaseInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * @param int $status
     * @return SubscriptionReleaseInterface
     */
    public function setStatus(int $status): SubscriptionReleaseInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }
}
