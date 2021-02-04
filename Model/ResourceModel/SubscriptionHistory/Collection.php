<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\SubscriptionHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory as SubscriptionHistoryResource;
use PayPal\Subscription\Model\SubscriptionHistory;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            SubscriptionHistory::class,
            SubscriptionHistoryResource::class
        );
    }
}
