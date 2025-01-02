<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\RecoveryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionsRecoveryCommand extends Command
{
    /**
     * @var RecoveryManager
     */
    private $_recoveryManager;

    public function __construct(RecoveryManager $recoveryManager)
    {
        $this->_recoveryManager = $recoveryManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscriptions-recovery')
            ->setDescription('Attempt to recover CEE incentive requests')
            ->setHelp('For incentive requests that can be (long or short distance), this command can be used to recover blocked requests.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_recoveryManager->execute();

        $output->writeln('The process was sucessfully completed');
    }
}
