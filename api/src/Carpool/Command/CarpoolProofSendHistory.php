<?php

namespace App\Carpool\Command;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Service\ProofManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarpoolProofSendHistory extends Command
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var ProofManager
     */
    private $_proofManager;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        ProofManager $proofManager
    ) {
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_proofManager = $proofManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:carpool:proof-send-history')
            ->setDescription('Sends proofs history to RPC.')
            ->setHelp('Sends proofs history to RPC.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $proofs = array_merge(
            $this->_carpoolProofRepository->findProofsToSendAsHistory(),
            $this->_carpoolProofRepository->findProofsToSendAsHistory(false)
        );

        $this->_proofManager->importProofs($proofs);
    }
}
