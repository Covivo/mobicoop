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
class CarpoolItemTest extends TestCase
{
    /**
     * @test
     */
    public function getCarpoolProof()
    {
        $carpoolItem = new CarpoolItem();

        $this->assertNull($carpoolItem->getCarpoolProof());                                 // There is no associated CarpoolProof

        $now = new \DateTime('now');

        $ask = new Ask();

        $carpoolItem->setItemDate($now);
        $carpoolItem->setAsk($ask);

        $carpoolProof = new CarpoolProof();
        $carpoolProof->setAsk($ask);
        $carpoolProof->setPickUpDriverDate($now);

        $this->assertInstanceOf(CarpoolProof::class, $carpoolItem->getCarpoolProof());      // There is an associated CarpoolProof
    }
}
