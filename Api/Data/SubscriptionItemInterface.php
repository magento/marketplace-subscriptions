<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * Interface SubscriptionItemInterface
 */
interface SubscriptionItemInterface
{
    public const SUB_ITEM_ID = 'id';
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const SKU = 'sku';
    public const PRICE = 'price';
    public const QUANTITY = 'qty';
    public const PRODUCT_ID = 'product_id';

    /**
     * Get subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * Set subscription id
     *
     * @param int $subscriptionId
     * @return SubscriptionItemInterface
     */
    public function setSubscriptionId(int $subscriptionId): SubscriptionItemInterface;

    /**
     * Get product sku
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Set product sku
     *
     * @param string $sku
     * @return SubscriptionItemInterface
     */
    public function setSku(string $sku): SubscriptionItemInterface;

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice(): float;

    /**
     * Set price
     *
     * @param float $price
     * @return SubscriptionItemInterface
     */
    public function setPrice(float $price): SubscriptionItemInterface;

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQty(): int;

    /**
     * Set quantity
     *
     * @param int $quantity
     * @return SubscriptionItemInterface
     */
    public function setQty(int $quantity): SubscriptionItemInterface;

    /**
     * Get product id
     *
     * @return int
     */
    public function getProductId(): int;

    /**
     * Set product id
     *
     * @param int $productId
     * @return SubscriptionItemInterface
     */
    public function setProductId(int $productId): SubscriptionItemInterface;
}
