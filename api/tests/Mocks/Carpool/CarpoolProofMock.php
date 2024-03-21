<?php

namespace App\Tests\Mocks\Carpool;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Geography\Entity\Address;

class CarpoolProofMock
{
    public static function getCarpoolProofEec(Ask $ask): CarpoolProof
    {
        $carpoolProof = new CarpoolProof();
        $carpoolProof->setAsk($ask);
        $carpoolProof->setPickUpDriverAddress(new Address());
        $carpoolProof->setPickUpDriverDate(new \DateTime());
        $carpoolProof->setDropOffDriverAddress(new Address());
        $carpoolProof->setDropOffDriverDate(new \DateTime());
        $carpoolProof->setType(CarpoolProof::TYPE_HIGH);
        $carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $carpoolProof->setPickUpDriverDate(new \DateTime());

        return $carpoolProof;
    }

    public static function getCarpoolProofEecStatusError(): CarpoolProof
    {
        $carpoolProof = new CarpoolProof();
        $carpoolProof->setStatus(CarpoolProof::STATUS_ERROR);
        $carpoolProof->setType(CarpoolProof::TYPE_MID);

        return $carpoolProof;
    }
}
