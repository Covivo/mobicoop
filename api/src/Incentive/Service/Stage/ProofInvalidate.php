<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Validator\SubscriptionValidator;
use Doctrine\ORM\EntityManagerInterface;

class ProofInvalidate extends Stage
{
    /**
     * @param LongDistanceJourney|ShortDistanceJourney $journey
     */
    private $_journey;

    public function __construct(EntityManagerInterface $em, TimestampTokenManager $timestampTokenManager, EecInstance $eecInstance, $journey)
    {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_journey = $journey;

        $this->_build();
    }

    public function execute(): void
    {
        if ($this->_subscription->isCommitmentJourney($this->_journey)) {
            if (SubscriptionValidator::canSubscriptionBeRecommited($this->_subscription)) {
                $stage = new AutoRecommitSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription);
                $stage->execute();

                return;
            }

            $stage = new ResetSubscription($this->_em, $this->_subscription);
            $stage->execute();

            return;
        }

        $this->_subscription->removeJourney($this->_journey);

        $this->_em->flush();
    }

    protected function _build()
    {
        $this->_subscription = $this->_journey->getSubscription();
    }
}
