<?php

namespace App\Incentive\Command;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Service\Manager\JourneyManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProofValidationCommand extends Command
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    public function __construct(CarpoolProofRepository $carpoolProofRepository, JourneyManager $journeyManager)
    {
        $this->_carpoolProofRepository = $carpoolProofRepository;

        $this->_journeyManager = $journeyManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:proof-validate')
            ->setDescription('Launches at EEC level, the manual validation of a proof.')
            ->setHelp('Launches at EEC level, the manual validation of a proof.')
            ->addArgument('proof_id', InputArgument::REQUIRED, 'The proof ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $carpoolProof = $this->_carpoolProofRepository->find($input->getArgument('proof_id'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The proof was not found');
        }

        $this->_journeyManager->validationOfProof($carpoolProof);
    }
}
