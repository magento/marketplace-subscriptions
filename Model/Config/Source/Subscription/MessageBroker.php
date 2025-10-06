<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Framework\Data\OptionSourceInterface;

class MessageBroker implements OptionSourceInterface
{
    public const MAGENTO_DATABASE_BROKER_CONFIG_VALUE = 1;
    public const RABBIT_MQ_BROKER_CONFIG_VALUE = 2;

    /**
     * Retrieve message broker options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => 'Magento Database',
                'value' => self::MAGENTO_DATABASE_BROKER_CONFIG_VALUE
            ],
            [
                'label' => 'Rabbit MQ',
                'value' => self::RABBIT_MQ_BROKER_CONFIG_VALUE
            ]
        ];
    }
}
