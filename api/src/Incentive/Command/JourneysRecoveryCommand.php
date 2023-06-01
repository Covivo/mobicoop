<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\JourneyRecoveryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JourneysRecoveryCommand extends Command
{
    /**
     * @var JourneyRecoveryManager
     */
    private $_journeyRecoveryManager;

    public function __construct(JourneyRecoveryManager $journeyRecoveryManager)
    {
        $this->_journeyRecoveryManager = $journeyRecoveryManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:journeys-recovery')
            ->setDescription('Executes EEC eligible evidence recovery.')
            ->setHelp('Executes EEC eligible evidence recovery.')
            ->addArgument('type', InputArgument::REQUIRED, 'The subscription type')
            ->addArgument('userId', InputArgument::OPTIONAL, 'User\'s ID whose proofs is to be recovered')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(json_encode(
            $this->_journeyRecoveryManager->executeProofsRecovery($input->getArgument('type'), $input->getArgument('userId'))
        ));
    }
}
