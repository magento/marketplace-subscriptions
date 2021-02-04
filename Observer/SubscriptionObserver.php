<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\Email\Subscription as SubscriptionEmail;
use PayPal\Subscription\Model\Subscription;

class SubscriptionObserver implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionEmail
     */
    private $subscriptionEmail;

    /**
     * SubscriptionUpdatedObserver constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SubscriptionEmail $subscriptionEmail
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionEmail $subscriptionEmail
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionEmail = $subscriptionEmail;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Subscription $subscription */
            $subscription = $observer->getEvent()->getSubscription();
            $customer = $this->customerRepository->getById($subscription->getCustomerId());
            if ($subscription->isObjectNew()) {
                $this->subscriptionEmail->new($subscription, $customer);
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Cannot find customer with ID %1', $subscription->getCustomerId()));
        }
    }
}
