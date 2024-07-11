<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Provider\CarpoolPaymentProvider;
use App\Incentive\Service\Provider\ProposalProvider;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;

class RecommitSubscription extends Stage
{
    private const PUSH_ONLY_MODE = true;

    /**
     * @var CarpoolProof|Proposal
     */
    private $_commitReferenceObject;

    /**
     * @var CarpoolPayment|CarpoolProof|Proposal
     */
    private $_validateReferenceObject;

    /**
     * @var LongDistanceJourney|ShortDistanceJourney
     */
    private $_journey;

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     * @param LongDistanceJourney|ShortDistanceJourney           $journey
     */
    public function __construct(
        EntityManagerInterface $em,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        TimestampTokenManager $timestampTokenManager,
        EecInstance $eecInstance,
        $subscription,
        $journey
    ) {
        $this->_em = $em;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_subscription = $subscription;
        $this->_journey = $journey;

        $this->_build();
    }

    public function execute()
    {
        $stage = $this->_subscription instanceof LongDistanceSubscription
            ? new CommitLDSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription, $this->_commitReferenceObject, self::PUSH_ONLY_MODE)
            : new CommitSDSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription, $this->_commitReferenceObject, self::PUSH_ONLY_MODE);

        if (!$stage->execute()) {
            return;
        }

        $stage = $this->_subscription instanceof LongDistanceSubscription
            ? new ValidateLDSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $this->_validateReferenceObject, self::PUSH_ONLY_MODE)
            : new ValidateSDSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $this->_validateReferenceObject, self::PUSH_ONLY_MODE);

        $stage->execute();
    }

    private function _build(): void
    {
        if ($this->_subscription instanceof ShortDistanceSubscription) {
            $this->_commitReferenceObject = $this->_validateReferenceObject = $this->_journey->getCarpoolProof();

            return;
        }

        $this->_commitReferenceObject = ProposalProvider::getProposalFromLdJourney($this->_journey);
        $this->_validateReferenceObject = CarpoolPaymentProvider::getCarpoolPaymentFromLdJourney($this->_journey);
    }
}
