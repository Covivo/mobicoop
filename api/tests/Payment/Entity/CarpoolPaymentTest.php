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
     * @var Ask
     */
    private $_ask;

    /**
     * @var CarpoolItem
     */
    private $_carpoolItem;

    /**
     * @var CarpoolPayment
     */
    private $_carpoolPayment;

    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    /**
     * @var \DateTime
     */
    private $_now;

    public function setUp(): void
    {
        $this->_now = new \DateTime('now');
        $this->_ask = new Ask();
        $this->_carpoolItem = new CarpoolItem();
        $this->_carpoolProof = new CarpoolProof();
        $this->_carpoolPayment = new CarpoolPayment();
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliant()
    {
        $this->_carpoolProof->setAsk($this->_ask);
        $this->_carpoolProof->setPickUpDriverDate($this->_now);

        $this->_carpoolItem->setAsk($this->_ask);
        $this->_carpoolItem->setItemDate($this->_now);

        $this->_carpoolPayment->addCarpoolItem($this->_carpoolItem);

        $this->assertFalse($this->_carpoolPayment->hasAtLeastAProofEECCompliant());        // There is no associated proof

        $this->_carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $this->_carpoolProof->setType(CarpoolProof::TYPE_HIGH);

        $this->assertTrue($this->_carpoolPayment->hasAtLeastAProofEECCompliant());         // The is an associated proof
    }

    /**
     * @test
     *
     * @depends hasAtLeastAProofEECCompliant
     */
    public function isEECCompliant()
    {
        $this->_carpoolProof->setAsk($this->_ask);
        $this->_carpoolProof->setPickUpDriverDate($this->_now);
        $this->_carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $this->_carpoolProof->setType(CarpoolProof::TYPE_HIGH);

        $this->_carpoolItem->setAsk($this->_ask);
        $this->_carpoolItem->setItemDate($this->_now);

        $this->_carpoolPayment->addCarpoolItem($this->_carpoolItem);

        $this->assertFalse($this->_carpoolPayment->isEECCompliant());                      // The CarpoolPayment doesn't have the right status or transaction ID

        $this->_carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
        $this->assertFalse($this->_carpoolPayment->isEECCompliant());                      // The CarpoolPayment have the right status but no transaction ID

        $this->_carpoolPayment->setStatus(CarpoolPayment::STATUS_FAILURE);
        $this->_carpoolPayment->setTransactionId(12548793);
        $this->assertFalse($this->_carpoolPayment->isEECCompliant());                      // The CarpoolPayment have a transaction ID but not the right status

        $this->_carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
        $this->assertTrue($this->_carpoolPayment->isEECCompliant());                       // The CarpoolPayment is EEC compliant
    }
}
