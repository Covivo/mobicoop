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
     * @var Ask
     */
    private $_ask;

    /**
     * @var CarpoolItem
     */
    private $_carpoolItem;

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
    }

    /**
     * @test
     */
    public function getCarpoolProof()
    {
        $this->assertNull($this->_carpoolItem->getCarpoolProof());                                  // There is no associated CarpoolProof

        $this->_carpoolItem->setItemDate($this->_now);
        $this->_carpoolItem->setAsk($this->_ask);

        $this->_carpoolProof->setAsk($this->_ask);
        $this->_carpoolProof->setPickUpDriverDate($this->_now);

        $this->assertInstanceOf(CarpoolProof::class, $this->_carpoolItem->getCarpoolProof());      // There is an associated CarpoolProof
    }
}
