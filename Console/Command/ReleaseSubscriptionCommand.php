<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Console\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Cron\Release;
use PayPal\Subscription\Model\ConfigurationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseSubscriptionCommand extends Command
{
    /**
     * ReleaseSubscriptionCommand constructor.
     *
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param PublisherInterface $publisher
     * @param ConfigurationInterface $configuration
     * @param string|null $name
     */
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly PublisherInterface $publisher,
        private readonly ConfigurationInterface $configuration,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $options = [
            new InputOption('id', null, InputOption::VALUE_REQUIRED, 'Subscription Entity ID to release')
        ];
        $this->setName('paypal:subscription:release');
        $this->setDescription(
            'Publish a single Subscription immediately to the Message Queue, regardless of next release date.'
        );
        $this->setDefinition($options);
        parent::configure();
    }

    /**
     * Publish single subscription to the message queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Searching for Subscription ID #{$input->getOption('id')}...");

        try {
            $configuredMessageBroker = $this->configuration->getMessageBroker();
            $subscription = $this->subscriptionRepository->getById(
                (int) $input->getOption('id')
            );
            $output->writeln("Found Subscription ID #{$input->getOption('id')}");
            $this->publisher->publish(Release::TOPIC_NAME_DB, $subscription);
            $output->writeln("Subscription ID #{$input->getOption('id')} published to Message Queue");
        } catch (NoSuchEntityException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
