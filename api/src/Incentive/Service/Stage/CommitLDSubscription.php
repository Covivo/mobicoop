<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitLDSubscription extends CommitSubscription
{
    public function __construct(
        EntityManagerInterface $em,
        TimestampTokenManager $timestampTokenManager,
        EventDispatcherInterface $eventDispatcher,
        EecInstance $eecInstance,
        LongDistanceSubscription $subscription,
        ?Proposal $proposal,
        bool $pushOnlyMode = false
    ) {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eventDispatcher = $eventDispatcher;

        $this->_eecInstance = $eecInstance;
        $this->_subscription = $subscription;
        $this->_proposal = $proposal;
        $this->_pushOnlyMode = $pushOnlyMode;

        $this->_build();
    }

    public function execute(): bool
    {
        if (is_null($this->_proposal)) {
            return false;
        }

        return $this->_commitSubscription();
    }
}
