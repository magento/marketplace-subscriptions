<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\Subscription;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            Subscription::class,
            SubscriptionResource::class
        );
    }
}
