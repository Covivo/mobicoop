<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use Doctrine\ORM\EntityManagerInterface;

class CommitSDSubscription extends CommitSubscription
{
    public function __construct(EntityManagerInterface $em, TimestampTokenManager $timestampTokenManager, EecInstance $eecInstance, ShortDistanceSubscription $subscription, CarpoolProof $carpoolProof, bool $pushOnlyMode = false)
    {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_subscription = $subscription;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;

        $this->_build();
    }

    public function execute()
    {
        $this->_commitSubscription();
    }
}
