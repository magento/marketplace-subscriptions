<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\SubscriptionItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem as SubscriptionItemResource;
use PayPal\Subscription\Model\SubscriptionItem;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            SubscriptionItem::class,
            SubscriptionItemResource::class
        );
    }
}
