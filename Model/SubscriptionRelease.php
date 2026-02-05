<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\SubscriptionReleaseInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease as SubscriptionReleaseResource;

class SubscriptionRelease extends AbstractModel implements SubscriptionReleaseInterface
{
    /**
     * Initialize subscription release resource
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(SubscriptionReleaseResource::class);
    }

    /**
     * Get subscription ID
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Set subscription ID
     *
     * @param int $subscriptionId
     * @return SubscriptionReleaseInterface
     */
    public function setSubscriptionId(int $subscriptionId): SubscriptionReleaseInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return SubscriptionReleaseInterface
     */
    public function setCustomerId(int $customerId): SubscriptionReleaseInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get order ID
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * Set order ID
     *
     * @param int $orderId
     * @return SubscriptionReleaseInterface
     */
    public function setOrderId(int $orderId): SubscriptionReleaseInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return SubscriptionReleaseInterface
     */
    public function setStatus(int $status): SubscriptionReleaseInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }
}
