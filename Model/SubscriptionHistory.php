<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory as SubscriptionHistoryResource;

class SubscriptionHistory extends AbstractModel implements SubscriptionHistoryInterface
{
    /**
     * @var string
     */
    public $_eventObject = 'subscriptionHistory';

    /**
     * @var string
     */
    public $_eventPrefix = 'paypal_subscription_history';

    /**
     * Initialize subscription history resource model
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(SubscriptionHistoryResource::class);
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
     * @param int $id
     * @return SubscriptionHistoryInterface
     */
    public function setSubscriptionId(int $id): SubscriptionHistoryInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $id);
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->getData(self::ACTION);
    }

    /**
     * Set action
     *
     * @param string $action
     * @return SubscriptionHistoryInterface
     */
    public function setAction(string $action): SubscriptionHistoryInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * Get action type
     *
     * @return string
     */
    public function getActionType(): string
    {
        return $this->getData(self::ACTION_TYPE);
    }

    /**
     * Set action type
     *
     * @param string $actionType
     * @return SubscriptionHistoryInterface
     */
    public function setActionType(string $actionType): SubscriptionHistoryInterface
    {
        return $this->setData(self::ACTION_TYPE, $actionType);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return SubscriptionHistoryInterface
     */
    public function setDescription(string $description): SubscriptionHistoryInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get admin ID
     *
     * @return int
     */
    public function getAdminId(): int
    {
        return (int) $this->getData(self::ADMIN_ID);
    }

    /**
     * Set admin ID
     *
     * @param int $adminId
     * @return SubscriptionHistoryInterface
     */
    public function setAdminId(int $adminId): SubscriptionHistoryInterface
    {
        return $this->setData(self::ADMIN_ID, $adminId);
    }

    /**
     * Get customer notified
     *
     * @return bool
     */
    public function getCustomerNotified(): bool
    {
        return (bool) $this->getData(self::NOTIFIED);
    }

    /**
     * Set customer notified
     *
     * @param int $customerNotified
     * @return SubscriptionHistoryInterface
     */
    public function setCustomerNotified(int $customerNotified): SubscriptionHistoryInterface
    {
        return $this->setData(self::NOTIFIED, $customerNotified);
    }

    /**
     * Get visible to customer
     *
     * @return bool
     */
    public function getVisibleToCustomer(): bool
    {
        return (bool) $this->getData(self::VISIBLE);
    }

    /**
     * Set visible to customer
     *
     * @param int $visible
     * @return SubscriptionHistoryInterface
     */
    public function setVisibleToCustomer(int $visible): SubscriptionHistoryInterface
    {
        return $this->setData(self::VISIBLE, $visible);
    }
}
