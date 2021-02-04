<?php

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;

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
     * Release constructor.
     *
     * @param Action\Context $context
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param PublisherInterface $publisher
     */
    public function __construct(
        Action\Context $context,
        SubscriptionRepositoryInterface $subscriptionRepository,
        PublisherInterface $publisher
    ) {
        parent::__construct($context);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->publisher = $publisher;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $subscription = $this->subscriptionRepository->getById((int)$id);
            $this->publisher->publish(\PayPal\Subscription\Cron\Release::TOPIC_NAME, $subscription);
            $this->messageManager->addSuccessMessage("Subscription {$id} released.");
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
