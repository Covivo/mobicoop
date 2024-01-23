<?php

namespace App\Incentive\Validator;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Interfaces\EecValidatorInterface;

class CarpoolProofValidator implements EecValidatorInterface
{
    /**
     * @var CarpoolProof
     */
    private $carpooProof;

    public function __construct(CarpoolProof $carpooProof)
    {
        $this->carpooProof = $carpooProof;
    }

    public function isEecCompliant(): bool
    {
        return CarpoolProof::STATUS_VALIDATED === $this->carpooProof->getStatus() && CarpoolProof::TYPE_HIGH === $this->carpooProof->getType();
    }
}
