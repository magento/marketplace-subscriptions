<?php

declare(strict_types=1);

namespace PayPal\Subscription\Console\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Cron\Release;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseSubscriptionCommand extends Command
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * ReleaseSubscriptionCommand constructor.
     *
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param PublisherInterface $publisher
     * @param null $name
     */
    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        PublisherInterface $publisher,
        $name = null
    ) {
        parent::__construct($name);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->publisher = $publisher;
    }

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Searching for Subscription ID #{$input->getOption('id')}...");

        try {
            $subscription = $this->subscriptionRepository->getById((int) $input->getOption('id'));
            $output->writeln("Found Subscription ID #{$input->getOption('id')}");
            $this->publisher->publish(Release::TOPIC_NAME, $subscription);
            $output->writeln("Subscription ID #{$input->getOption('id')} published to Message Queue");
        } catch (NoSuchEntityException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
