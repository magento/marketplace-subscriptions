<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Cron\Release as ReleaseCron;
use PayPal\Subscription\Model\Config\Source\Subscription\MessageBroker;
use PayPal\Subscription\Model\ConfigurationInterface;

class Release extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'PayPal_Subscription::subscription_release';

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * Release constructor.
     *
     * @param Action\Context $context
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param PublisherInterface $publisher
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        Action\Context $context,
        SubscriptionRepositoryInterface $subscriptionRepository,
        PublisherInterface $publisher,
        ConfigurationInterface $configuration
    ) {
        parent::__construct($context);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->publisher = $publisher;
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $subscription = $this->subscriptionRepository->getById((int)$id);
            $configuredMessageBroker = $this->configuration->getMessageBroker();
            $topic = $configuredMessageBroker === MessageBroker::RABBIT_MQ_BROKER_CONFIG_VALUE ?
                ReleaseCron::TOPIC_NAME :
                ReleaseCron::TOPIC_NAME_DB;
            $this->publisher->publish(
                $topic,
                $subscription
            );
            $this->messageManager->addSuccessMessage("Subscription {$id} released.");
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
