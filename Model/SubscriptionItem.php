<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem as SubscriptionItemResource;

class SubscriptionItem extends AbstractModel implements SubscriptionItemInterface
{
    protected function _construct()
    {
        $this->_init(SubscriptionItemResource::class);
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
     * @return SubscriptionItemInterface
     */
    public function setSubscriptionId(int $id): SubscriptionItemInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $id);
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param string $sku
     * @return SubscriptionItemInterface
     */
    public function setSku(string $sku): SubscriptionItemInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return (float) $this->getData(self::PRICE);
    }

    /**
     * @param float $price
     * @return SubscriptionItemInterface
     */
    public function setPrice(float $price): SubscriptionItemInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @return int
     */
    public function getQty(): int
    {
        return (int) $this->getData(self::QUANTITY);
    }

    /**
     * @param int $quantity
     * @return SubscriptionItemInterface
     */
    public function setQty(int $quantity): SubscriptionItemInterface
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int) $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return SubscriptionItemInterface
     */
    public function setProductId(int $productId): SubscriptionItemInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
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
     * @return SubscriptionItemInterface
     */
    public function setOrderId(int $orderId): SubscriptionItemInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }
}
