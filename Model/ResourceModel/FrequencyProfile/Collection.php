<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\FrequencyProfile;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPal\Subscription\Model\FrequencyProfile;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyResource;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            FrequencyProfile::class,
            FrequencyResource::class
        );
    }
}
