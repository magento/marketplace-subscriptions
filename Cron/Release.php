<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Cron;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Subscription\Api\SubscriptionManagementInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\ConfigurationInterface;
use PayPal\Subscription\Model\Email\Release as ReleaseEmail;
use PayPal\Subscription\Model\ReleaseCronRunningFlagInterface;
use PayPal\Subscription\Model\ReleaseConsumer;
use Psr\Log\LoggerInterface;

class Release
{
    public const TOPIC_NAME = 'paypal.subscription.release';
    public const TOPIC_NAME_DB = 'paypal.subscription.release.db';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SubscriptionManagementInterface
     */
    private $subscriptionManagement;

    /**
     * @var ReleaseEmail
     */
    private $releaseEmail;

    /**
     * @var ReleaseCronRunningFlagInterface
     */
    private $releaseCronRunningFlag;

    /**
     * @var ReleaseConsumer
     */
    private $releaseConsumer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * Release constructor
     *
     * @param ConfigurationInterface $configuration
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriptionManagementInterface $subscriptionManagement
     * @param ReleaseEmail $releaseEmail
     * @param ReleaseCronRunningFlagInterface $releaseCronRunningFlag
     * @param ReleaseConsumer $releaseConsumer
     * @param LoggerInterface $logger
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        ConfigurationInterface $configuration,
        CustomerRepositoryInterface $customerRepository,
        SubscriptionManagementInterface $subscriptionManagement,
        ReleaseEmail $releaseEmail,
        ReleaseCronRunningFlagInterface $releaseCronRunningFlag,
        ReleaseConsumer $releaseConsumer,
        LoggerInterface $logger,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->configuration = $configuration;
        $this->customerRepository = $customerRepository;
        $this->subscriptionManagement = $subscriptionManagement;
        $this->releaseEmail = $releaseEmail;
        $this->releaseCronRunningFlag = $releaseCronRunningFlag;
        $this->releaseConsumer = $releaseConsumer;
        $this->logger = $logger;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Release the subscriptions via cron
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(): void
    {
        if ($this->releaseCronRunningFlag->getIsReleaseCronRunning() === true) {
            return;
        }
        $this->releaseCronRunningFlag->setIsReleaseCronRunning(true);
        try {
            $releases = $this->subscriptionManagement->collectReleases(
                date(
                    'Y-m-d 00:00:00',
                    strtotime(
                        date('Y-m-d 00:00:00') . ' - 1 year'
                    )
                ),
                date('Y-m-d 23:59:59')
            );
            foreach ($releases as $release) {
                $this->releaseConsumer->execute($release);
                if ($release->getReminderEmailSent() === true) {
                    // Latest release successfully generated, set email reminder flag to false for next release.
                    $release->setReminderEmailSent(false);
                    $this->subscriptionRepository->save($release);
                }
            }
        } catch (Exception $exception) {
            $this->handleException($exception, "An error occurred during release generation");
        }
        $reminderTiming = $this->configuration->getReleaseReminderEmailTiming();
        if ($reminderTiming !== 0) {
            try {
                $subscriptionsForReleaseReminderEmails = $this->subscriptionManagement->collectReleases(
                    date(
                        'Y-m-d 00:00:00',
                        strtotime(
                            date('Y-m-d 00:00:00') . ' + ' . $reminderTiming . 'days'
                        )
                    ),
                    date(
                        'Y-m-d 23:59:59',
                        strtotime(
                            date('Y-m-d 23:59:59') . ' + ' . $reminderTiming . 'days'
                        )
                    ),
                    false
                );
                foreach ($subscriptionsForReleaseReminderEmails as $subscriptionsForReleaseReminderEmail) {
                    $customer = $this->customerRepository->getById(
                        (int) $subscriptionsForReleaseReminderEmail->getCustomerId()
                    );
                    $this->releaseEmail->success(
                        $customer,
                        $subscriptionsForReleaseReminderEmail
                    );
                    // Reminder email successfully sent, set flag to true to prevent duplicate email per cron run.
                    $subscriptionsForReleaseReminderEmail->setReminderEmailSent(true);
                    $this->subscriptionRepository->save($subscriptionsForReleaseReminderEmail);
                }
            } catch (Exception $exception) {
                $this->handleException($exception, "An error occurred during reminder email generation");
            }
        }
        $this->releaseCronRunningFlag->setIsReleaseCronRunning(false);
    }

    /**
     * Log error, remove running flag and throw exception to ensure cron listed as failure in cron_schedule table.
     *
     * @param Exception $exception
     * @param string $logMessage
     * @throws Exception
     */
    private function handleException(Exception $exception, string $logMessage)
    {
        $this->logger->error($logMessage, [
            'exception_message' => $exception->getMessage()
        ]);
        $this->releaseCronRunningFlag->setIsReleaseCronRunning(false);
        throw $exception;
    }
}
