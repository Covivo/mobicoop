<?php

namespace App\ExternalService\Command;

use App\ExternalService\Interfaces\DTO\CarpoolProofDto;
use App\ExternalService\Interfaces\SendProof;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarpoolProofSend extends Command
{
    private $_sendProof;

    public function __construct(
        SendProof $sendProof
    ) {
        $this->_sendProof = $sendProof;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:external-service:carpool-proof-send')
            ->setDescription('Sends proofs by external service.')
            ->setHelp('Sends proofs by external service.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $carpoolProof = new CarpoolProofDto();
        $this->_sendProof->send($carpoolProof);
    }
}
