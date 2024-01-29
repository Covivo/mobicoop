<?php

namespace App\Tests\Mocks\Carpool;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;

class EecCarpoolProof
{
    public static function getCarpoolProof(Ask $ask): CarpoolProof
    {
        $carpoolProof = new CarpoolProof();
        $carpoolProof->setAsk($ask);
        $carpoolProof->setType(CarpoolProof::TYPE_HIGH);
        $carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $carpoolProof->setPickUpDriverDate(new \DateTime());

        return $carpoolProof;
    }

    public static function getCarpoolProofStatusError(): CarpoolProof
    {
        $carpoolProof = new CarpoolProof();
        $carpoolProof->setStatus(CarpoolProof::STATUS_ERROR);
        $carpoolProof->setType(CarpoolProof::TYPE_MID);

        return $carpoolProof;
    }
}
