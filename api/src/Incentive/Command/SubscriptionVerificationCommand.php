<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionVerificationCommand extends Command
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
            ->setName('app:incentive:subscription-verify')
            ->setDescription('Request verification of a subscription registered with mobConnect.')
            ->setHelp('Verify, with moB Connect, the subscription corresponding to the parameters.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The subscription type')
            ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The subscription ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_subscriptionManager->verifySubscriptionFromType($input->getOption('type'), $input->getOption('subscription'));
    }
}
