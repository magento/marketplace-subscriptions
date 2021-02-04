<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SubscriptionHistory extends AbstractDb
{
    private const TABLE_NAME = 'paypal_subs_subscription_history';
    private const ID_FIELD = 'id';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD);
    }
}
