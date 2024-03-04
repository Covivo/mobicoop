<?php

namespace App\ExternalService\Command;

use App\ExternalService\Interfaces\DTO\CarpoolProof\CarpoolProofDto;
use App\ExternalService\Interfaces\DTO\CarpoolProof\WaypointDto;
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
        echo 'CarpoolProofSend'.PHP_EOL;

        $carpoolProofDto = new CarpoolProofDto();
        $carpoolProofDto->setId(1);
        $carpoolProofDto->setDistance(10000);
        $pickUpDriverDto = new WaypointDto();
        $pickUpDriverDto->setLat(18.0146548);
        $pickUpDriverDto->setLon(6.0146548);
        $dropOffDriverDto = new WaypointDto();
        $dropOffDriverDto->setLat(18.0146548);
        $dropOffDriverDto->setLon(6.0146548);
        $carpoolProofDto->setPickUpDriver($pickUpDriverDto);
        $carpoolProofDto->setPickUpPassenger($pickUpDriverDto);
        $carpoolProofDto->setDropOffDriver($pickUpDriverDto);
        $carpoolProofDto->setDropOffPassenger($pickUpDriverDto);

        var_dump($this->_sendProof->send($carpoolProofDto));
    }
}
