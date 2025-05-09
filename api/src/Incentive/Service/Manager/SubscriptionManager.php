<?php

namespace App\Incentive\Service\Manager;

use App\Action\Entity\Action;
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Communication\Service\NotificationManager;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Event\SubscriptionNotReadyToVerifyEvent;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
use App\Incentive\Resource\EecEligibility;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\NotificationsPresenceChecker;
use App\Incentive\Service\Provider\JourneyProvider;
use App\Incentive\Service\Stage\CreateSubscription;
use App\Incentive\Service\Stage\PatchSubscription;
use App\Incentive\Service\Stage\ProofInvalidate;
use App\Incentive\Service\Stage\ProofRecovery;
use App\Incentive\Service\Stage\ProofValidate;
use App\Incentive\Service\Stage\ResetSubscription;
use App\Incentive\Service\Stage\ValidateLDSubscription;
use App\Incentive\Service\Stage\VerifySubscription;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\Incentive\Validator\APIAuthenticationValidator;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Incentive\Validator\SubscriptionValidator;
use App\Incentive\Validator\UserValidator;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Repository\CarpoolItemRepository;
use App\Payment\Repository\CarpoolPaymentRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionManager extends MobConnectManager
{
    public const VERIFICATION_STATUS_PENDING = 0;
    public const VERIFICATION_STATUS_ENDED = 1;

    /**
     * @var EecInstance
     */
    protected $_eecInstance;

    /**
     * @var EventDispatcherInterface
     */
    protected $_eventDispatcher;

    /**
     * @var CarpoolItemRepository
     */
    protected $_carpoolItemRepository;

    /**
     * @var CarpoolProofRepository
     */
    protected $_carpoolProofRepository;

    /**
     * @var CarpoolPaymentRepository
     */
    private $_carpoolPaymentRepository;

    /**
     * @var NotificationManager
     */
    private $_notificationManager;

    /**
     * @var LongDistanceSubscriptionRepository
     */
    private $_longDistanceSubscriptionRepository;

    /**
     * @var ShortDistanceSubscriptionRepository
     */
    private $_shortDistanceSubscriptionRepository;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    /**
     * @var SubscriptionValidation
     */
    private $_subscriptionValidation;

    /**
     * @var bool
     */
    private $_eecSendWarningIncompleteProfile;

    /**
     * @var int
     */
    private $_eecSendWarningIncompleteProfileTime;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationManager $notificationManager,
        SubscriptionValidation $subscriptionValidation,
        UserValidation $userValidation,
        LoggerService $loggerService,
        InstanceManager $instanceManager,
        TimestampTokenManager $timestampTokenManager,
        CarpoolItemRepository $carpoolItemRepository,
        CarpoolPaymentRepository $carpoolPaymentRepository,
        CarpoolProofRepository $carpoolProofRepository,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository,
        ShortDistanceJourneyRepository $shortDistanceJourneyRepository,
        ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository,
        UserRepository $userRepository,
        bool $eecSendWarningIncompleteProfile,
        int $eecSendWarningIncompleteProfileTime
    ) {
        parent::__construct($em, $instanceManager, $loggerService);

        $this->_eventDispatcher = $eventDispatcher;
        $this->_notificationManager = $notificationManager;

        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_carpoolPaymentRepository = $carpoolPaymentRepository;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_longDistanceJourneyRepository = $longDistanceJourneyRepository;
        $this->_longDistanceSubscriptionRepository = $longDistanceSubscriptionRepository;
        $this->_shortDistanceJourneyRepository = $shortDistanceJourneyRepository;
        $this->_shortDistanceSubscriptionRepository = $shortDistanceSubscriptionRepository;
        $this->_userRepository = $userRepository;
        $this->_subscriptionValidation = $subscriptionValidation;
        $this->_userValidation = $userValidation;
        $this->_eecInstance = $instanceManager->getEecInstance();
        $this->_eecSendWarningIncompleteProfile = $eecSendWarningIncompleteProfile;
        $this->_eecSendWarningIncompleteProfileTime = $eecSendWarningIncompleteProfileTime;
    }

    /**
     * STEP 5 - Creating incentives requests.
     *
     * For the authenticated user, if needed, creates the CEE sheets.
     */
    public function createSubscriptions(User $user)
    {
        if (!$this->_instanceManager->isEecServiceAvailable()) {
            return;
        }

        $this->setDriver($user);

        $this->_createSubscription(Subscription::TYPE_SHORT);
        $this->_createSubscription(Subscription::TYPE_LONG);
    }

    public function getUserEECEligibility(User $user): EecEligibility
    {
        $this->setDriver($user);

        $userEligibility = new EecEligibility($user);

        $userEligibility->setLongDistanceJourneysNumber(count($this->getEECCompliantProofsObtainedSinceDate(LongDistanceSubscription::SUBSCRIPTION_TYPE)));
        $userEligibility->setShortDistanceJourneysNumber(count($this->getEECCompliantProofsObtainedSinceDate(ShortDistanceSubscription::SUBSCRIPTION_TYPE)));
        $userEligibility->setLongDistanceDrivingLicenceNumberDoublon($this->_longDistanceSubscriptionRepository->getDuplicatePropertiesNumber('drivingLicenceNumber', $user->getDrivingLicenceNumber()));
        $userEligibility->setLongDistancePhoneDoublon($this->_longDistanceSubscriptionRepository->getDuplicatePropertiesNumber('telephone', $user->getTelephone()));
        $userEligibility->setShortDistanceDrivingLicenceNumberDoublon($this->_shortDistanceSubscriptionRepository->getDuplicatePropertiesNumber('drivingLicenceNumber', $user->getDrivingLicenceNumber()));
        $userEligibility->setShortDistancePhoneDoublon($this->_shortDistanceSubscriptionRepository->getDuplicatePropertiesNumber('telephone', $user->getTelephone()));
        $userEligibility->setAddressFullyCompleted(UserValidator::isUserAddressFullyCompleted($user));

        return $userEligibility;
    }

    public function updateSubscriptionsAddress(User $user)
    {
        $this->setDriver($user);

        if (!is_null($this->getDriver()->getLongDistanceSubscription())) {
            $this->getDriver()->getLongDistanceSubscription()->updateAddress();
        }

        if (!is_null($this->getDriver()->getShortDistanceSubscription())) {
            $this->getDriver()->getShortDistanceSubscription()->updateAddress();
        }

        $this->_em->flush();
    }

    public function updateSubscriptionDrivingLicenceNumber(User $user)
    {
        $stage = new PatchSubscription($this->_em, $this->_eecInstance, $user, SpecificFields::DRIVING_LICENCE_NUMBER);
        $stage->execute();
    }

    public function updateSubscriptionPhone(User $user)
    {
        $stage = new PatchSubscription($this->_em, $this->_eecInstance, $user, SpecificFields::PHONE_NUMBER);
        $stage->execute();
    }

    public function processingVersionTransitionalPeriods()
    {
        /**
         * @var SubscriptionDefinitionInterface[]
         */
        $definitions = array_merge(LongDistanceSubscription::getAvailableDefinitions(), ShortDistanceSubscription::getAvailableDefinitions());

        foreach ($definitions as $definition) {
            if (!$definition::isDeadlineOver()) {
                continue;
            }

            $definition::manageTransition($this->_em, $this->_longDistanceSubscriptionRepository);
        }
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function resetSubscription($subscription): void
    {
        $stage = new ResetSubscription($this->_em, $subscription);
        $stage->execute();
    }

    /**
     * STEP 9 - Commit a subscription.
     *
     * @param CarpoolProof|Proposal                              $referenceObject
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function commitSubscription($subscription, $referenceObject, bool $pushOnlyMode = false, bool $noResetMode = false): void
    {
        if (!APIAuthenticationValidator::isAuthenticationValid($subscription->getUser())) {
            return;
        }

        if (!$noResetMode && $subscription->isCommited()) {
            $stage = new ResetSubscription($this->_em, $subscription);
            $stage->execute();
        }

        $commitClass = $subscription instanceof LongDistanceSubscription
            ? 'App\\Incentive\\Service\\Stage\\CommitLDSubscription'
            : 'App\\Incentive\\Service\\Stage\\CommitSDSubscription';

        $stage = new $commitClass($this->_em, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $subscription, $referenceObject, $pushOnlyMode);
        $stage->execute();
    }

    /**
     * STEP 17 - Validate a subscription.
     *
     * @param CarpoolPayment|CarpoolProof $referenceObject
     */
    public function validateSubscription($referenceObject, bool $pushOnlyMode = false): void
    {
        if ($referenceObject instanceof CarpoolPayment) {
            if (!$this->_eecInstance->isLdFeaturesAvailable()) {
                return;
            }

            $stage = new ValidateLDSubscription($this->_em, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $referenceObject, $pushOnlyMode);
            $stage->execute();

            return;
        }

        if (!$this->_eecInstance->isSdFeaturesAvailable()) {
            return;
        }

        $stage = new ProofValidate($this->_em, $this->_carpoolPaymentRepository, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $referenceObject, $pushOnlyMode);
        $stage->execute();
    }

    /**
     * Step 17 - Unvalidate proof.
     */
    public function invalidateProof(CarpoolProof $carpoolProof): void
    {
        if (CarpoolProofValidator::isEecCompliant($carpoolProof)) {
            return;
        }

        $journeyProvider = new JourneyProvider($this->_longDistanceJourneyRepository, $this->_shortDistanceJourneyRepository);
        $journey = $journeyProvider->getJourneyFromCarpoolProof($carpoolProof);

        if (
            is_null($journey)
            || !$journey->getSubscription()
            || !$journey->getSubscription()->getUser()
            || !APIAuthenticationValidator::isAuthenticationValid($journey->getSubscription()->getUser())
        ) {
            return;
        }

        $stage = new ProofInvalidate($this->_em, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $journey);
        $stage->execute();
    }

    public function proofsRecover(string $subscriptionType, ?int $userId): void
    {
        if (!is_null($userId)) {
            $user = $this->_em->getRepository(User::class)->find($userId);

            if (is_null($user)) {
                throw new NotFoundHttpException('The requested user was not found');
            }

            $stage = new ProofRecovery($this->_em, $this->_carpoolItemRepository, $this->_carpoolPaymentRepository, $this->_carpoolProofRepository, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $user, $subscriptionType);
            $stage->execute();

            return;
        }
    }

    /**
     * STEP 20 - Verify ready subscriptions.
     */
    public function verifySubscriptions()
    {
        $subscriptions = array_merge(
            $this->_shortDistanceSubscriptionRepository->getReadyForVerify(),
            $this->_longDistanceSubscriptionRepository->getReadyForVerify()
        );

        foreach ($subscriptions as $key => $subscription) {
            $this->_verifySubscription($subscription);
        }
    }

    /**
     * STEP 20 - Vérify a subscription from it's type and ID.
     */
    public function verifySubscriptionFromType(?string $subscriptionType, ?string $subscriptionId)
    {
        if (is_null($subscriptionType) || is_null($subscriptionId)) {
            return $this->verifySubscriptions();
        }

        $this->_subscriptionValidation->checkSubscriptionTypeValidity($subscriptionType);

        $this->_subscriptionValidation->checkSubscriptionIdValidity($subscriptionId);

        $subscriptionId = intval($subscriptionId);

        switch ($subscriptionType) {
            case 'long':
                $repository = $this->_em->getRepository(LongDistanceSubscription::class);

                break;

            case 'short':
                $repository = $this->_em->getRepository(ShortDistanceSubscription::class);

                break;
        }

        $subscription = $repository->find($subscriptionId);

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The requested subscription was not found');
        }

        $this->_verifySubscription($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function subscriptionNotReadyToVerify($subscription)
    {
        $notificationPresenceChecker = new NotificationsPresenceChecker(
            $this->_em,
            $subscription->getUser(),
            Action::ACTION_CEE_SUBSCRIPTION_NOT_READY_TO_VERRIFY
        );

        if (
            $this->_eecSendWarningIncompleteProfile
            && !$notificationPresenceChecker->hasLastNotificationBeenSendAfterDeadline($this->_eecSendWarningIncompleteProfileTime)
        ) {
            $this->_notificationManager->notifies(Action::ACTION_CEE_SUBSCRIPTION_NOT_READY_TO_VERRIFY, $subscription->getUser(), $subscription);
        }
    }

    /**
     * STEP 5 - Create a subscription from it's type.
     *
     * @throws \LogicException
     */
    protected function _createSubscription(string $subscriptionType): void
    {
        if (!Subscription::isTypeAllowed($subscriptionType)) {
            throw new \LogicException('eec_subscriptionType_unallowed');
        }

        if (!is_null($this->getDriver()->{'get'.ucfirst($subscriptionType).'DistanceSubscription'}())) {
            return;
        }

        if (
            !$this->_instanceManager->{'is'.ucfirst($subscriptionType).'SubscriptionAvailable'}()
            || !$this->isDriverAccountReadyForSubscription($subscriptionType)
        ) {
            return;
        }

        $stage = new CreateSubscription($this->_em, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_loggerService, $this->_eecInstance, $this->_driver, $subscriptionType);
        $stage->execute();
    }

    /**
     * STEP 20 - Vérify a subscription from it's ID.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    protected function _verifySubscription($subscription): void
    {
        if (SubscriptionValidator::isReadyToVerify($subscription)) {
            if (
                !SubscriptionValidator::isAddressValid($subscription)
                || !SubscriptionValidator::isPhoneNumberValid($subscription)
                || !SubscriptionValidator::isDrivingLicenceNumberValid($subscription)
            ) {
                $event = new SubscriptionNotReadyToVerifyEvent($subscription);
                $this->_eventDispatcher->dispatch(SubscriptionNotReadyToVerifyEvent::NAME, $event);

                return;
            }

            $stage = new VerifySubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $subscription);
            $stage->execute();
        }
    }
}
