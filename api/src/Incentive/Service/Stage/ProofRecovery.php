<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Provider\CarpoolPaymentProvider;
use App\Incentive\Validator\CarpoolPaymentValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Repository\CarpoolItemRepository;
use App\Payment\Repository\CarpoolPaymentRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProofRecovery extends Stage
{
    /**
     * @var CarpoolItemRepository
     */
    protected $_carpoolItemRepository;

    /**
     * @var CarpoolProofRepository
     */
    protected $_carpoolProofRepository;

    /**
     * @var User
     */
    protected $_user;

    /**
     * @var string
     */
    protected $_subscriptionType;

    public function __construct(
        EntityManagerInterface $em,
        CarpoolItemRepository $carpoolItemRepository,
        CarpoolPaymentRepository $carpoolPaymentRepository,
        CarpoolProofRepository $carpoolProofRepository,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        TimestampTokenManager $timestampTokenManager,
        EventDispatcherInterface $eventDispatcher,
        EecInstance $eecInstance,
        User $user,
        string $subscriptionType
    ) {
        $this->_em = $em;
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_carpoolPaymentRepository = $carpoolPaymentRepository;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eventDispatcher = $eventDispatcher;

        $this->_eecInstance = $eecInstance;

        $this->_user = $user;
        $this->_subscriptionType = $subscriptionType;
    }

    public function execute()
    {
        // We recover the missing timestamp tokens available at moBConnect
        $this->_subscription = LongDistanceSubscription::TYPE_LONG === $this->_subscriptionType
            ? $this->_user->getLongDistanceSubscription() : $this->_user->getShortDistanceSubscription();

        $this->_recoveryProofs();
    }

    protected function _recoveryProofs()
    {
        switch (true) {
            case $this->_subscription instanceof LongDistanceSubscription:
                /**
                 * @var CarpoolItem[]
                 */
                $carpoolItems = $this->_carpoolItemRepository->findUserEECEligibleItem($this->_user);

                foreach ($carpoolItems as $carpoolItem) {
                    if (
                        is_null($this->_subscription->getCommitmentProofDate())
                        && empty($this->_subscription->getJourneys())
                    ) {
                        $proposal = $carpoolItem->getProposalAccordingUser($this->_user);

                        $subscription = !is_null($proposal->getUser()) && !is_null($proposal->getUser()->getLongDistanceSubscription())
                            ? $proposal->getUser()->getLongDistanceSubscription()
                            : null;

                        if (is_null($subscription)) {
                            return null;
                        }

                        $stage = new CommitLDSubscription($this->_em, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $subscription, $proposal);
                        $stage->execute();

                        return;
                    }

                    $carpoolPayment = CarpoolPaymentProvider::getCarpoolPaymentFromCarpoolItem($this->_carpoolPaymentRepository, $carpoolItem);

                    if (!is_null($carpoolPayment) && CarpoolPaymentValidator::isStatusEecCompliant($carpoolPayment)) {
                        $stage = new ValidateLDSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $carpoolPayment, false, true);
                        $stage->execute();
                    }
                }

                break;

            case $this->_subscription instanceof ShortDistanceSubscription:
                $carpoolProofs = $this->_carpoolProofRepository->findUserCEEEligibleProof($this->_user);

                foreach ($carpoolProofs as $carpoolProof) {
                    if (
                        is_null($this->_subscription->getCommitmentProofDate())
                        && empty($this->_subscription->getJourneys())
                    ) {
                        $subscription = !is_null($carpoolProof->getDriver()) && !is_null($carpoolProof->getDriver()->getShortDistanceSubscription())
                            ? $carpoolProof->getDriver()->getShortDistanceSubscription()
                            : null;

                        if (is_null($subscription)) {
                            return null;
                        }

                        $stage = new CommitSDSubscription($this->_em, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $subscription, $carpoolProof);
                        $stage->execute();

                        return;
                    }

                    $stage = new ProofValidate($this->_em, $this->_carpoolPaymentRepository, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $carpoolProof, false, true);
                    $stage->execute();
                }

                break;
        }
    }
}
