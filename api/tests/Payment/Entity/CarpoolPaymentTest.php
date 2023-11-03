<?php

namespace App\Payment\Entity;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolPaymentTest extends TestCase
{
    /**
     * @test
     */
    public function isEECCompliant()
    {
        $now = new \DateTime('now');

        $ask = new Ask();

        $carpoolProof = new CarpoolProof();
        $carpoolProof->setAsk($ask);
        $carpoolProof->setPickUpDriverDate($now);
        $carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $carpoolProof->setType(CarpoolProof::TYPE_HIGH);

        $carpoolItem = new CarpoolItem();
        $carpoolItem->setAsk($ask);
        $carpoolItem->setItemDate($now);

        $carpoolPayment = new CarpoolPayment();
        $carpoolPayment->addCarpoolItem($carpoolItem);

        $this->assertFalse($carpoolPayment->isEECCompliant());                      // The CarpoolPayment doesn't have the right status or transaction ID

        $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
        $this->assertFalse($carpoolPayment->isEECCompliant());                      // The CarpoolPayment have the right status but no transaction ID

        $carpoolPayment->setStatus(CarpoolPayment::STATUS_FAILURE);
        $carpoolPayment->setTransactionId(12548793);
        $this->assertFalse($carpoolPayment->isEECCompliant());                      // The CarpoolPayment have a transaction ID but not the right status

        $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
        $this->assertTrue($carpoolPayment->isEECCompliant());                       // The CarpoolPayment is EEC compliant
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliant()
    {
        $now = new \DateTime('now');

        $ask = new Ask();

        $carpoolProof = new CarpoolProof();
        $carpoolProof->setAsk($ask);
        $carpoolProof->setPickUpDriverDate($now);

        $carpoolItem = new CarpoolItem();
        $carpoolItem->setAsk($ask);
        $carpoolItem->setItemDate($now);

        $carpoolPayment = new CarpoolPayment();
        $carpoolPayment->addCarpoolItem($carpoolItem);

        $this->assertFalse($carpoolPayment->hasAtLeastAProofEECCompliant());        // There is no associated proof

        $carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $carpoolProof->setType(CarpoolProof::TYPE_HIGH);

        $this->assertTrue($carpoolPayment->hasAtLeastAProofEECCompliant());         // The is an associated proof
    }
}
