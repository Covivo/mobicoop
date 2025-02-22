<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Validator\CarpoolProofValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitSDSubscription extends CommitSubscription
{
    public function __construct(
        EntityManagerInterface $em,
        TimestampTokenManager $timestampTokenManager,
        EventDispatcherInterface $eventDispatcher,
        EecInstance $eecInstance,
        ShortDistanceSubscription $subscription,
        CarpoolProof $carpoolProof,
        bool $pushOnlyMode = false
    ) {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eventDispatcher = $eventDispatcher;

        $this->_eecInstance = $eecInstance;
        $this->_subscription = $subscription;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;

        $this->_build();
    }

    public function execute(): bool
    {
        if (!CarpoolProofValidator::isCarpoolProofDataComplete($this->_carpoolProof)) {
            return false;
        }

        return $this->_commitSubscription();
    }
}
