<?php

namespace App\Incentive\Service\Validator;

use App\Carpool\Entity\Ask;
use App\Incentive\Validator\CarpoolPaymentValidator;
use App\Payment\Entity\CarpoolPayment;
use App\Tests\Mocks\Carpool\EecCarpoolProof;
use App\Tests\Mocks\Payment\EecCarpoolItem;
use App\Tests\Mocks\Payment\EecCarpoolPayment;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolPaymentValidatorTest extends TestCase
{
    /**
     * @var CarpoolPayment
     */
    private $_carpoolPayment;

    /**
     * @var CarpoolPayment
     */
    private $_carpoolPaymentEecCompliant;

    public function setUp(): void
    {
        $this->_carpoolPayment = new CarpoolPayment();

        $this->_carpoolPaymentEecCompliant = EecCarpoolPayment::getCarpoolPayment();

        $ask = new Ask();

        EecCarpoolItem::getCarpoolItem($this->_carpoolPaymentEecCompliant, $ask);
        EecCarpoolProof::getCarpoolProof($ask);
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliantBool()
    {
        $this->assertIsBool(CarpoolPaymentValidator::hasAtLeastAProofEECCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliantFalse()
    {
        $this->assertIsBool(CarpoolPaymentValidator::hasAtLeastAProofEECCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function hasAtLeastAProofEECCompliantTrue()
    {
        $this->assertIsBool(CarpoolPaymentValidator::hasAtLeastAProofEECCompliant($this->_carpoolPaymentEecCompliant));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isStatusEecCompliantBool()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isStatusEecCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function isStatusEecCompliantFalse()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isStatusEecCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function isStatusEecCompliantTrue()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isStatusEecCompliant($this->_carpoolPaymentEecCompliant));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isTransactionIdEecCompliantBool()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isTransactionIdEecCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function isTransactionIdEecCompliantFalse()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isTransactionIdEecCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function isTransactionIdEecCompliantTrue()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isTransactionIdEecCompliant($this->_carpoolPaymentEecCompliant));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isEecCompliantBool()
    {
        $this->assertIsBool(CarpoolPaymentValidator::isEecCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function isEecCompliantFalse()
    {
        $this->assertFalse(CarpoolPaymentValidator::isEecCompliant($this->_carpoolPayment));
    }

    /**
     * @test
     */
    public function isEecCompliantTrue()
    {
        $this->assertTrue(CarpoolPaymentValidator::isEecCompliant($this->_carpoolPaymentEecCompliant));
    }
}
