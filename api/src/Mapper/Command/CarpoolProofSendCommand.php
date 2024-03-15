<?php

namespace App\Mapper\Command;

use App\Carpool\Event\CarpoolProofCreatedEvent;
use App\Mapper\Interfaces\CarpoolProof;
use App\Tests\Mapper\Mock\CarpoolProof as MockCarpoolProof;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarpoolProofSendCommand extends Command
{
    private $_carpoolProofMapper;

    public function __construct(
        CarpoolProof $carpoolProofMapper
    ) {
        $this->_carpoolProofMapper = $carpoolProofMapper;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:mapper:carpool-proof-send')
            ->setDescription('Sends proofs by external service.')
            ->setHelp('Sends proofs by external service.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 'CarpoolProofSend'.PHP_EOL;

        $event = new CarpoolProofCreatedEvent(MockCarpoolProof::getCarpoolProof());
        $this->_carpoolProofMapper->map($event);
    }
}
