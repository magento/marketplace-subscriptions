<?php

declare(strict_types=1);

namespace PayPal\Subscription\Cron;

use Magento\Framework\MessageQueue\PublisherInterface;
use PayPal\Subscription\Api\SubscriptionManagementInterface;

class Release
{
    public const TOPIC_NAME = 'paypal.subscription.release';

    /**
     * @var SubscriptionManagementInterface
     */
    private $subscriptionManagement;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * Release constructor.
     *
     * @param SubscriptionManagementInterface $subscriptionManagement
     * @param PublisherInterface $publisher
     */
    public function __construct(
        SubscriptionManagementInterface $subscriptionManagement,
        PublisherInterface $publisher
    ) {
        $this->subscriptionManagement = $subscriptionManagement;
        $this->publisher = $publisher;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $releases = $this->subscriptionManagement->collectReleases(
            date('Y-m-d 00:00:00'),
            date('Y-m-d 23:59:59')
        );

        foreach ($releases as $release) {
            $this->publisher->publish(self::TOPIC_NAME, $release);
        }
    }
}
