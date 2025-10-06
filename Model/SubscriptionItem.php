<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem as SubscriptionItemResource;

class SubscriptionItem extends AbstractModel implements SubscriptionItemInterface
{
    /**
     * Initialize subscription item resource
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(SubscriptionItemResource::class);
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
     * @return SubscriptionItemInterface
     */
    public function setSubscriptionId(int $id): SubscriptionItemInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $id);
    }

    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku(): string
    {
        return $this->getData(self::SKU);
    }

    /**
     * Set SKU
     *
     * @param string $sku
     * @return SubscriptionItemInterface
     */
    public function setSku(string $sku): SubscriptionItemInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice(): float
    {
        return (float) $this->getData(self::PRICE);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return SubscriptionItemInterface
     */
    public function setPrice(float $price): SubscriptionItemInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get qty
     *
     * @return int
     */
    public function getQty(): int
    {
        return (int) $this->getData(self::QUANTITY);
    }

    /**
     * Set qty
     *
     * @param int $quantity
     * @return SubscriptionItemInterface
     */
    public function setQty(int $quantity): SubscriptionItemInterface
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * Get product ID
     *
     * @return int
     */
    public function getProductId(): int
    {
        return (int) $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product ID
     *
     * @param int $productId
     * @return SubscriptionItemInterface
     */
    public function setProductId(int $productId): SubscriptionItemInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
}
