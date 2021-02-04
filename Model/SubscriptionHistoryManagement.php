<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Api\Data\UserInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterfaceFactory;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\SubscriptionHistoryManagementInterface;
use PayPal\Subscription\Api\SubscriptionHistoryRepositoryInterface;

class SubscriptionHistoryManagement implements SubscriptionHistoryManagementInterface
{
    /**
     * @var SubscriptionHistoryInterfaceFactory
     */
    private $subscriptionHistoryFactory;

    /**
     * @var SubscriptionHistoryRepositoryInterface
     */
    private $subscriptionHistoryRepository;

    /**
     * SubscriptionHistoryManagement constructor.
     *
     * @param SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory
     * @param SubscriptionHistoryRepositoryInterface $subscriptionHistoryRepository
     */
    public function __construct(
        SubscriptionHistoryInterfaceFactory $subscriptionHistoryFactory,
        SubscriptionHistoryRepositoryInterface $subscriptionHistoryRepository
    ) {
        $this->subscriptionHistoryFactory = $subscriptionHistoryFactory;
        $this->subscriptionHistoryRepository = $subscriptionHistoryRepository;
    }

    /**
     * @param UserInterface $customer
     * @param SubscriptionInterface $subscription
     * @param string $action
     * @return SubscriptionHistoryInterface
     * @throws LocalizedException
     */
    public function recordCustomerHistory(
        UserInterface $customer,
        SubscriptionInterface $subscription,
        string $action
    ): SubscriptionHistoryInterface {
        try {
            /** @var SubscriptionHistory $history */
            $history = $this->subscriptionHistoryFactory->create();
            $history->setSubscriptionId((int) $subscription->getId());
            $history->setAction($action);
            $history->setCompletedBy('customer');
            $this->subscriptionHistoryRepository->save($history);
            return $history;
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__('Unable to log history. %1', $e->getMessage()));
        }
    }

    /**
     * @param UserInterface $admin
     * @param SubscriptionInterface $subscription
     * @param string $action
     * @param int $customerNotified
     * @param int $isVisibleToCustomer
     * @return SubscriptionHistoryInterface
     * @throws LocalizedException
     */
    public function recordAdminHistory(
        UserInterface $admin,
        SubscriptionInterface $subscription,
        string $action,
        int $customerNotified,
        int $isVisibleToCustomer
    ): SubscriptionHistoryInterface {
        try {
            /** @var SubscriptionHistory $history */
            $history = $this->subscriptionHistoryFactory->create();
            $history->setSubscriptionId((int) $subscription->getId());
            $history->setAction($action);
            $history->setCompletedBy('admin');
            $history->setCustomerNotified($customerNotified);
            $history->setVisibleToCustomer($isVisibleToCustomer);
            $this->subscriptionHistoryRepository->save($history);
            return $history;
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__('Unable to log history. %1', $e->getMessage()));
        }
    }
}
