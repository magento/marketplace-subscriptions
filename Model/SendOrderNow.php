<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\MessageQueue\PublisherInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\SendOrderNowInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Cron\Release;
use PayPal\Subscription\Model\Config\Source\Subscription\MessageBroker;

class SendOrderNow implements SendOrderNowInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * SkipNextSubscriptionOrder constructor
     *
     * @param ConfigurationInterface $configuration
     * @param PublisherInterface $publisher
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        ConfigurationInterface $configuration,
        PublisherInterface $publisher,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->configuration = $configuration;
        $this->publisher = $publisher;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        int $subscriptionId,
        int $customerId
    ): SubscriptionInterface {
        /** @var SubscriptionInterface $subscription */
        $subscription = $this->subscriptionRepository->getById($subscriptionId);
        $configuredMessageBroker = $this->configuration->getMessageBroker();
        if ($configuredMessageBroker === MessageBroker::RABBIT_MQ_BROKER_CONFIG_VALUE) {
            $topic = Release::TOPIC_NAME;
        } else {
            $topic = Release::TOPIC_NAME_DB;
        }
        $this->publisher->publish(
            $topic,
            $subscription
        );
        return $subscription;
    }
}
