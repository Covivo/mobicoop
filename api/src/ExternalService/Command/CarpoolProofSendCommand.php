<?php

namespace App\ExternalService\Command;

use App\ExternalService\Interfaces\DTO\CarpoolProof\CarpoolProofDto;
use App\ExternalService\Interfaces\DTO\CarpoolProof\DriverDto;
use App\ExternalService\Interfaces\DTO\CarpoolProof\PassengerDto;
use App\ExternalService\Interfaces\DTO\CarpoolProof\WaypointDto;
use App\ExternalService\Interfaces\SendProof;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarpoolProofSendCommand extends Command
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
        $pickUpDriverDto->setDatetime(\DateTime::createFromFormat('Ymd H:i:s', '20240801 12:00:00'));
        $dropOffDriverDto = new WaypointDto();
        $dropOffDriverDto->setLat(18.0146548);
        $dropOffDriverDto->setLon(6.0146548);
        $dropOffDriverDto->setDatetime(\DateTime::createFromFormat('Ymd H:i:s', '20240801 12:00:00'));
        $carpoolProofDto->setPickUpDriver($pickUpDriverDto);
        $carpoolProofDto->setPickUpPassenger($pickUpDriverDto);
        $carpoolProofDto->setDropOffDriver($pickUpDriverDto);
        $carpoolProofDto->setDropOffPassenger($pickUpDriverDto);

        $driver = new DriverDto();
        $driver->setId(2);
        $driver->setGivenName('Jean-Michel');
        $driver->setLastName('Test');
        $driver->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $driver->setRevenue(18);
        $driver->setPhone('0303030303');
        $carpoolProofDto->setDriver($driver);

        $passenger = new PassengerDto();
        $passenger->setId(2);
        $passenger->setGivenName('Francis-Daniel');
        $passenger->setLastName('Test');
        $passenger->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $passenger->setSeats(1);
        $passenger->setContribution(18);
        $passenger->setPhone('0606060606');
        $carpoolProofDto->setPassenger($passenger);

        var_dump($this->_sendProof->send($carpoolProofDto));
    }
}
