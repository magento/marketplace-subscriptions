<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory as SubscriptionHistoryResource;

class SubscriptionHistory extends AbstractModel implements SubscriptionHistoryInterface
{
    public $_eventObject = 'subscriptionHistory';

    public $_eventPrefix = 'paypal_subscription_history';

    protected function _construct()
    {
        $this->_init(SubscriptionHistoryResource::class);
    }

    /**
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * @param int $id
     * @return SubscriptionHistoryInterface
     */
    public function setSubscriptionId(int $id): SubscriptionHistoryInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $id);
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->getData(self::ACTION);
    }

    /**
     * @param string $action
     * @return SubscriptionHistoryInterface
     */
    public function setAction(string $action): SubscriptionHistoryInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @return string
     */
    public function getActionType(): string
    {
        return $this->getData(self::ACTION_TYPE);
    }

    /**
     * @param string $actionType
     * @return SubscriptionHistoryInterface
     */
    public function setActionType(string $actionType): SubscriptionHistoryInterface
    {
        return $this->setData(self::ACTION_TYPE, $actionType);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return SubscriptionHistoryInterface
     */
    public function setDescription(string $description): SubscriptionHistoryInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return int
     */
    public function getAdminId(): int
    {
        return (int) $this->getData(self::ADMIN_ID);
    }

    /**
     * @param int $adminId
     * @return SubscriptionHistoryInterface
     */
    public function setAdminId(int $adminId): SubscriptionHistoryInterface
    {
        return $this->setData(self::ADMIN_ID, $adminId);
    }

    /**
     * @return bool
     */
    public function getCustomerNotified(): bool
    {
        return (bool) $this->getData(self::NOTIFIED);
    }

    /**
     * @param int $customerNotified
     * @return SubscriptionHistoryInterface
     */
    public function setCustomerNotified(int $customerNotified): SubscriptionHistoryInterface
    {
        return $this->setData(self::NOTIFIED, $customerNotified);
    }

    /**
     * @return bool
     */
    public function getVisibleToCustomer(): bool
    {
        return (bool) $this->getData(self::VISIBLE);
    }

    /**
     * @param int $visible
     * @return SubscriptionHistoryInterface
     */
    public function setVisibleToCustomer(int $visible): SubscriptionHistoryInterface
    {
        return $this->setData(self::VISIBLE, $visible);
    }
}
