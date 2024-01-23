<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use Doctrine\ORM\EntityManager;

class ValidateSDSubscription extends UpdateStage
{
    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    public function __construct(EntityManager $em, ShortDistanceSubscription $subscription, CarpoolProof $carpoolProof, bool $pushOnlyMode = false)
    {
        $this->_em = $em;
        $this->_subscription = $subscription;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;
    }

    public function execute() {}
}
