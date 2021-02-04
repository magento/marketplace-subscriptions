<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

class Status extends AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        return [
            ['label' => __('Active'), 'value' => SubscriptionInterface::STATUS_ACTIVE],
            ['label' => __('Paused'), 'value' => SubscriptionInterface::STATUS_PAUSED],
            ['label' => __('Cancelled'), 'value' => SubscriptionInterface::STATUS_CANCELLED],
            ['label' => __('Expired'), 'value' => SubscriptionInterface::STATUS_EXPIRED]
        ];
    }
}
