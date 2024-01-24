<?php

namespace App\Carpool\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolProofTest extends TestCase
{
    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    public function setUp(): void
    {
        $this->_carpoolProof = new CarpoolProof();
    }

    /**
     * @test
     *
     * @dataProvider dataForEECCompliance
     */
    public function isEECCompliant(int $status, string $type, bool $expectedResponse)
    {
        $this->_carpoolProof->setStatus($status);
        $this->_carpoolProof->setType($type);

        $this->assertSame($expectedResponse, $this->_carpoolProof->isEECCompliant());
    }

    public function dataForEECCompliance()
    {
        return [
            [CarpoolProof::STATUS_VALIDATED, CarpoolProof::TYPE_LOW, false],
            [CarpoolProof::STATUS_VALIDATED, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_INITIATED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_PENDING, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_SENT, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_CANCELED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_ACQUISITION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_NORMALIZATION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_FRAUD_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_EXPIRED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_CANCELED_BY_OPERATOR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNDER_CHECKING, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_VALIDATED, CarpoolProof::TYPE_HIGH, true],
        ];
    }

    public function dataForProofDowngraded()
    {
        return [
            [CarpoolProof::STATUS_VALIDATED, CarpoolProof::TYPE_LOW, true],
            [CarpoolProof::STATUS_VALIDATED, CarpoolProof::TYPE_MID, true],
            [CarpoolProof::STATUS_VALIDATED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_INITIATED, CarpoolProof::TYPE_LOW, false],
            [CarpoolProof::STATUS_INITIATED, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_INITIATED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_PENDING, CarpoolProof::TYPE_LOW, false],
            [CarpoolProof::STATUS_PENDING, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_PENDING, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_SENT, CarpoolProof::TYPE_LOW, false],
            [CarpoolProof::STATUS_SENT, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_SENT, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_ERROR, CarpoolProof::TYPE_LOW, false],
            [CarpoolProof::STATUS_ERROR, CarpoolProof::TYPE_LOW, false],
            [CarpoolProof::STATUS_ERROR, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_CANCELED, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_CANCELED, CarpoolProof::TYPE_MID, false],
            [CarpoolProof::STATUS_CANCELED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_ACQUISITION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_ACQUISITION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_ACQUISITION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_NORMALIZATION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_NORMALIZATION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_NORMALIZATION_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_FRAUD_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_FRAUD_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_FRAUD_ERROR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_EXPIRED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_EXPIRED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_EXPIRED, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_CANCELED_BY_OPERATOR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_CANCELED_BY_OPERATOR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_CANCELED_BY_OPERATOR, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNDER_CHECKING, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNDER_CHECKING, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNDER_CHECKING, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNKNOWN, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNKNOWN, CarpoolProof::TYPE_HIGH, false],
            [CarpoolProof::STATUS_UNKNOWN, CarpoolProof::TYPE_HIGH, false],
        ];
    }
}
