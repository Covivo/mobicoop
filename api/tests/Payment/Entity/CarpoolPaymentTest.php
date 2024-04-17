<?php

namespace App\Payment\Entity;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Tests\Mocks\Geography\AddressMock;
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
    public function hasAtLeastAProofEECCompliantBool()
    {
        $this->assertIsBool($this->_carpoolPayment->hasAtLeastAProofEECCompliant());
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliantFalse()
    {
        $this->_carpoolProof->setAsk($this->_ask);
        $this->_carpoolProof->setPickUpDriverDate($this->_now);

        $this->_carpoolItem->setAsk($this->_ask);
        $this->_carpoolItem->setItemDate($this->_now);

        $this->_carpoolPayment->addCarpoolItem($this->_carpoolItem);

        $this->assertFalse($this->_carpoolPayment->hasAtLeastAProofEECCompliant());        // There is no associated proof

        // $this->_carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        // $this->_carpoolProof->setType(CarpoolProof::TYPE_HIGH);

        // $this->assertTrue($this->_carpoolPayment->hasAtLeastAProofEECCompliant());         // The is an associated proof
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliantTrue()
    {
        $this->_carpoolProof->setAsk($this->_ask);

        $this->_carpoolProof->setPickUpDriverAddress(AddressMock::getHomeAddress());
        $this->_carpoolProof->setPickUpDriverDate($this->_now);
        $this->_carpoolProof->setDropOffDriverAddress(AddressMock::getHomeAddress());
        $this->_carpoolProof->setDropOffDriverDate($this->_now);

        $this->_carpoolProof->setStatus(CarpoolProof::STATUS_VALIDATED);
        $this->_carpoolProof->setType(CarpoolProof::TYPE_HIGH);

        $this->_carpoolItem->setAsk($this->_ask);
        $this->_carpoolItem->setItemDate($this->_now);

        $this->_carpoolPayment->addCarpoolItem($this->_carpoolItem);

        $this->assertTrue($this->_carpoolPayment->hasAtLeastAProofEECCompliant());         // The is an associated proof
    }
}
