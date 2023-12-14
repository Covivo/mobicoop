<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionTransitionalPeriodCommand extends Command
{
    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        parent::__construct();

        $this->_subscriptionManager = $subscriptionManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-transitional-period')
            ->setDescription('Processes transitional periods of subscription versions.')
            ->setHelp('Processes transitional periods of subscription versions.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_subscriptionManager->processingVersionTransitionalPeriods();

        $output->writeln('The subscriptions have been updated');
    }
}
