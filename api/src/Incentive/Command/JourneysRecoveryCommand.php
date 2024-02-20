<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JourneysRecoveryCommand extends Command
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
            ->setName('app:incentive:journeys-recovery')
            ->setDescription('Executes EEC eligible evidence recovery.')
            ->setHelp('Executes EEC eligible evidence recovery.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The subscription type')
            ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'User\'s ID whose proofs is to be recovered')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(json_encode(
            $this->_subscriptionManager->proofsRecover($input->getOption('type'), $input->getOption('userId'))
        ));
    }
}
