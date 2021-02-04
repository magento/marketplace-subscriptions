<?php

declare(strict_types=1);

namespace PayPal\Subscription\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var string $channel
     */
    private static $channel = 'channel';

    /**
     * @var string $channelValue
     */
    private static $channelValue = 'GENE_Magento2_BT_Subscriptions';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        return [
            self::$channel => self::$channelValue
        ];
    }
}
