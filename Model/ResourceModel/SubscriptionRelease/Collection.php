<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\SubscriptionRelease;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease as SubscriptionReleaseResource;
use PayPal\Subscription\Model\SubscriptionRelease;

/**
 * @api
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            SubscriptionRelease::class,
            SubscriptionReleaseResource::class
        );
    }
}
