<?php

namespace App\Carpool\Command;

use App\Carpool\Repository\CarpoolProofRepository;
use App\DataProvider\Service\RpcApiManager;
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
     * @var RpcApiManager
     */
    private $_rpcApiManager;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        RpcApiManager $rpcApiManager
    ) {
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_rpcApiManager = $rpcApiManager;

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

        $provider = $this->_rpcApiManager->getProvider();
        $provider->importProofs($proofs);
    }
}
