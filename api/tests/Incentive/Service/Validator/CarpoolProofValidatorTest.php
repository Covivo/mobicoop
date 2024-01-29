<?php

namespace App\Incentive\Service\Validator;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Tests\Mocks\Carpool\AskMock;
use App\Tests\Mocks\Carpool\CarpoolProofMock;
use App\Tests\Mocks\Incentive\LdSubscriptionMock;
use App\Tests\Mocks\Payment\CarpoolItemMock;
use App\Tests\Mocks\Payment\CarpoolPaymentMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolProofValidatorTest extends TestCase
{
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
     * @var CarpoolProof
     */
    private $_carpoolProofEecCompliant;

    /**
     * @var CarpoolProof
     */
    private $_carpoolProofStatusError;

    public function setUp(): void
    {
        $ask = AskMock::getAskEec();

        $this->_carpoolPayment = CarpoolPaymentMock::getCarpoolPaymentEec();
        $this->_carpoolItem = CarpoolItemMock::getCarpoolItemEec($this->_carpoolPayment, $ask);

        $this->_carpoolProof = new CarpoolProof();

        $this->_carpoolProofEecCompliant = CarpoolProofMock::getCarpoolProofEec($ask);
        $this->_carpoolProofStatusError = CarpoolProofMock::getCarpoolProofEecStatusError();
    }

    /**
     * @test
     */
    public function isCarpoolProofStatusEecCompliantBool()
    {
        $this->assertIsBool(CarpoolProofValidator::isCarpoolProofStatusEecCompliant($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isCarpoolProofStatusEecCompliantFalse()
    {
        $this->assertFalse(CarpoolProofValidator::isCarpoolProofStatusEecCompliant($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isCarpoolProofStatusEecCompliantTrue()
    {
        $this->assertTrue(CarpoolProofValidator::isCarpoolProofStatusEecCompliant($this->_carpoolProofEecCompliant));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isCarpoolProofTypeEecCompliantBool()
    {
        $this->assertIsBool(CarpoolProofValidator::isCarpoolProofTypeEecCompliant($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isCarpoolProofTypeEecCompliantFalse()
    {
        $this->assertFalse(CarpoolProofValidator::isCarpoolProofTypeEecCompliant($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isCarpoolProofTypeEecCompliantTrue()
    {
        $this->assertTrue(CarpoolProofValidator::isCarpoolProofTypeEecCompliant($this->_carpoolProofEecCompliant));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isStatusErrorBool()
    {
        $this->assertIsBool(CarpoolProofValidator::isStatusError($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isStatusErrorFalse()
    {
        $this->assertIsBool(CarpoolProofValidator::isStatusError($this->_carpoolProofEecCompliant));
    }

    /**
     * @test
     */
    public function isStatusErrorTrue()
    {
        $this->assertIsBool(CarpoolProofValidator::isStatusError($this->_carpoolProofStatusError));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isDowngradedTypeBool()
    {
        $this->assertIsBool(CarpoolProofValidator::isDowngradedType($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isDowngradedTypeFalse()
    {
        $this->assertFalse(CarpoolProofValidator::isDowngradedType($this->_carpoolProofEecCompliant));
    }

    /**
     * @test
     */
    public function isDowngradedTypeTrue()
    {
        $this->assertTrue(CarpoolProofValidator::isDowngradedType($this->_carpoolProofStatusError));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isEecCompliantBool()
    {
        $this->assertIsBool(CarpoolProofValidator::isEecCompliant($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isEecCompliantFalse()
    {
        $this->assertFalse(CarpoolProofValidator::isEecCompliant($this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isEecCompliantTrue()
    {
        $this->assertTrue(CarpoolProofValidator::isEecCompliant($this->_carpoolProofEecCompliant));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isCarpoolProofSubscriptionCommitmentProofBool()
    {
        $this->assertIsBool(CarpoolProofValidator::isCarpoolProofSubscriptionCommitmentProof(LdSubscriptionMock::getNewSubscription(), $this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isCarpoolProofSubscriptionCommitmentProofFalse()
    {
        $this->assertFalse(CarpoolProofValidator::isCarpoolProofSubscriptionCommitmentProof(LdSubscriptionMock::getNewSubscription(), $this->_carpoolProof));
    }

    /**
     * @test
     */
    public function isCarpoolProofSubscriptionCommitmentProofTrue()
    {
        $this->assertTrue(CarpoolProofValidator::isCarpoolProofSubscriptionCommitmentProof(LdSubscriptionMock::getValidatedSubscription($this->_carpoolItem, $this->_carpoolPayment), $this->_carpoolProof));
    }
}
