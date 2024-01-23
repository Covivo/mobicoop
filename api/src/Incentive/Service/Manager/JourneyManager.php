<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\Subscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Provider\JourneyProvider;
use App\Incentive\Service\Stage\CommitLDSubscription;
use App\Incentive\Service\Stage\CommitSDSubscription;
use App\Incentive\Service\Stage\ProofInvalidate;
use App\Incentive\Service\Stage\ProofValidate;
use App\Incentive\Service\Stage\ValidateLDSubscription;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class JourneyManager extends MobConnectManager
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var CarpoolItemRepository
     */
    private $_carpoolItemRepository;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        CarpoolItemRepository $carpoolItemRepository,
        EntityManagerInterface $em,
        InstanceManager $instanceManager,
        LoggerService $loggerService,
        TimestampTokenManager $timestampTokenManager,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        ShortDistanceJourneyRepository $shortDistanceJourneyRepository
    ) {
        parent::__construct($em, $instanceManager, $loggerService);

        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_longDistanceJourneyRepository = $longDistanceJourneyRepository;
        $this->_shortDistanceJourneyRepository = $shortDistanceJourneyRepository;
    }

    public function userProofsRecovery(User $driver, string $subscriptionType): bool
    {
        $this->setDriver($driver);

        $result = false;

        switch ($subscriptionType) {
            case MobConnectManager::LONG_SUBSCRIPTION_TYPE:
                /**
                 * @var CarpoolItem[]
                 */
                $carpoolItems = $this->_carpoolItemRepository->findUserEECEligibleItem($driver);

                foreach ($carpoolItems as $item) {
                    if (
                        is_null($driver->getLongDistanceSubscription()->getCommitmentProofDate())
                        && empty($driver->getLongDistanceSubscription()->getJourneys())
                    ) {
                        $proposal = $item->getProposalAccordingUser($this->getDriver());

                        $this->declareFirstLongDistanceJourney($proposal);
                    }

                    $carpoolPayment = $this->_getCarpoolPaymentFromCarpoolItem($item);

                    if (!is_null($carpoolPayment) && CarpoolPayment::STATUS_SUCCESS === $carpoolPayment->getStatus()) {
                        $this->receivingElectronicPayment($carpoolPayment);
                    }

                    $result = true;
                }

                break;

            case MobConnectManager::SHORT_SUBSCRIPTION_TYPE:
                $carpoolProofs = $this->_carpoolProofRepository->findUserCEEEligibleProof($driver, $subscriptionType);

                foreach ($carpoolProofs as $carpoolProof) {
                    if (
                        is_null($driver->getShortDistanceSubscription()->getCommitmentProofDate())
                        && empty($driver->getShortDistanceSubscription()->getJourneys())
                    ) {
                        $this->declareFirstShortDistanceJourney($carpoolProof);
                    }

                    $this->validationOfProof($carpoolProof);

                    $result = true;
                }

                break;
        }

        return $result;
    }

    /**
     * Step 9 - Long distance journey.
     *
     * @return bool|LongDistanceJourney
     */
    public function declareFirstLongDistanceJourney(Proposal $proposal): ?LongDistanceJourney
    {
        $subscription = !is_null($proposal->getUser()) && !is_null($proposal->getUser()->getLongDistanceSubscription())
            ? $proposal->getUser()->getLongDistanceSubscription()
            : null;

        if (is_null($subscription)) {
            return null;
        }

        $stage = new CommitLDSubscription($this->_em, $this->_timestampTokenManager, $this->_instanceManager->getEecInstance(), $subscription, $proposal);

        return $stage->execute();
    }

    /**
     * Step 9 - Short distance journey.
     *
     * @return bool|ShortDistanceJourney
     */
    public function declareFirstShortDistanceJourney(CarpoolProof $carpoolProof): ?ShortDistanceJourney
    {
        $subscription = !is_null($carpoolProof->getDriver()) && !is_null($carpoolProof->getDriver()->getShortDistanceSubscription())
            ? $carpoolProof->getDriver()->getShortDistanceSubscription()
            : null;

        if (is_null($subscription)) {
            return null;
        }

        $stage = new CommitSDSubscription($this->_em, $this->_timestampTokenManager, $this->_instanceManager->getEecInstance(), $subscription, $carpoolProof);

        return $stage->execute();
    }

    /**
     * Step 17 - Electronic payment is validated for a long distance journey. All carpooling compliant with the CEE standard will be processed.
     */
    public function receivingElectronicPayment(CarpoolPayment $carpoolPayment)
    {
        $stage = new ValidateLDSubscription($this->_em, $this->_longDistanceJourneyRepository, $this->_instanceManager->getEecInstance(), $carpoolPayment);
        $stage->execute();
    }

    /**
     * Step 17 - Validation of proof for a short distance journey.
     */
    public function validationOfProof(CarpoolProof $carpoolProof)
    {
        $stage = new ProofValidate($this->_em, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_instanceManager->getEecInstance(), $carpoolProof);
        $stage->execute();
    }

    /**
     * Step 17 - Unvalidation of proof.
     * Resets a short distance subscription when the commitment journey has not been validated by the RPC.
     */
    public function invalidateProof(CarpoolProof $carpoolProof): void
    {
        if (CarpoolProofValidator::isEecCompliant($carpoolProof)) {
            return;
        }

        $journeyProvider = new JourneyProvider($this->_longDistanceJourneyRepository);
        $journey = $journeyProvider->getJourneyFromCarpoolProof($carpoolProof);

        if (is_null($journey)) {
            return;
        }

        $stage = new ProofInvalidate($this->_em, $this->_timestampTokenManager, $this->_instanceManager->getEecInstance(), $journey);
        $stage->execute();
    }

    private function _getCarpoolPaymentFromCarpoolItem(CarpoolItem $carpoolItem): ?CarpoolPayment
    {
        $distance = $carpoolItem->getRelativeDistance();

        if (is_null($distance)) {
            return null;
        }

        if (is_null($this->getDistanceType($distance))) {
            return null;
        }

        $carpoolPayments = array_values(array_filter($carpoolItem->getCarpoolPayments(), function (CarpoolPayment $carpoolPayment) {
            return $carpoolPayment->isEecCompliant();
        }));

        return !(empty($carpoolPayments)) ? $carpoolPayments[0] : null;
    }
}
