<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Provider\CarpoolPaymentProvider;
use App\Incentive\Service\Provider\ProposalProvider;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;

class RecommitSubscription extends Stage
{
    private const LD_SHORTCUT_NAME = 'LD';
    private const SD_SHORTCUT_NAME = 'SD';

    private const PUSH_ONLY_MODE = true;

    /**
     * @var CarpoolProof|Proposal
     */
    private $_commitReferenceObject;

    /**
     * @var CarpoolPayment|Proposal
     */
    private $_validateReferenceObject;

    /**
     * @var LongDistanceJourney|ShortDistanceJOurney
     */
    private $_journey;

    /**
     * @var string
     */
    private $_shortcutName = self::LD_SHORTCUT_NAME;

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function __construct(EntityManagerInterface $em, TimestampTokenManager $timestampTokenManager, EecInstance $eecInstance, $subscription)
    {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_subscription = $subscription;

        $this->_build();
    }

    public function execute()
    {
        $this->_recommitSubscription();
    }

    protected function _recommitSubscription()
    {
        $commitClassName = 'App\\Incentive\\Service\\Stage\\Commit'.$this->_shortcutName.'Subscription';
        $stage = new $commitClassName($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription, $this->_commitReferenceObject, self::PUSH_ONLY_MODE);
        $stage->execute();

        $validateClassName = 'App\\Incentive\\Service\\Stage\\Validate'.$this->_shortcutName.'Subscription';
        $stage = new $validateClassName($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription, $this->_validateReferenceObject, self::PUSH_ONLY_MODE);
        $stage->execute();
    }

    private function _build(): void
    {
        $this->_journey = $this->_subscription->getJourneys()->toArray()[0];

        if (!$this->_subscription instanceof LongDistanceSubscription) {
            $this->_shortcutName = self::SD_SHORTCUT_NAME;

            $this->_commitReferenceObject = $this->_validateReferenceObject = $this->_journey->getCarpoolProof();

            return;
        }

        $this->_commitReferenceObject = ProposalProvider::getProposalFromLdJourney($this->_journey);
        $this->_validateReferenceObject = CarpoolPaymentProvider::getCarpoolPaymentFromLdJourney($this->_journey);
    }
}
