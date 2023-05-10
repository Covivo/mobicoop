<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionVerificationCommand extends Command
{
    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->_subscriptionManager = $subscriptionManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-verification')
            ->setDescription('Request verification of a subscription registered with mobConnect.')
            ->setHelp('Verify, with moB Connect, the subscription corresponding to the parameters.')
            ->addArgument('subscriptionType', InputArgument::REQUIRED, 'The type of subscription')
            ->addArgument('subscriptionId', InputArgument::REQUIRED, 'The id of the subscription')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_subscriptionManager->verifySubscriptionFromControllerCommand($input->getArgument('subscriptionType'), $input->getArgument('subscriptionId'));
    }
}
