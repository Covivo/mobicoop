<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
use App\Incentive\Resource\EecEligibility;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Provider\JourneyProvider;
use App\Incentive\Service\Provider\SubscriptionProvider;
use App\Incentive\Service\Stage\AutoRecommitSubscription;
use App\Incentive\Service\Stage\CreateSubscription;
use App\Incentive\Service\Stage\ProofInvalidate;
use App\Incentive\Service\Stage\ProofRecovery;
use App\Incentive\Service\Stage\ResetSubscription;
use App\Incentive\Service\Stage\VerifySubscription;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionManager extends MobConnectManager
{
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_REJECTED = 'REJETEE';
    public const STATUS_VALIDATED = 'VALIDEE';

    public const VERIFICATION_STATUS_PENDING = 0;
    public const VERIFICATION_STATUS_ENDED = 1;

    /**
     * @var EecInstance
     */
    protected $_eecInstance;

    /**
     * @var CarpoolItemRepository
     */
    protected $_carpoolItemRepository;

    /**
     * @var CarpoolProofRepository
     */
    protected $_carpoolProofRepository;

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

    public function __construct(
        EntityManagerInterface $em,
        SubscriptionValidation $subscriptionValidation,
        UserValidation $userValidation,
        LoggerService $loggerService,
        InstanceManager $instanceManager,
        TimestampTokenManager $timestampTokenManager,
        CarpoolItemRepository $carpoolItemRepository,
        CarpoolProofRepository $carpoolProofRepository,
        LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository,
        ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($em, $instanceManager, $loggerService);

        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_longDistanceSubscriptionRepository = $longDistanceSubscriptionRepository;
        $this->_shortDistanceSubscriptionRepository = $shortDistanceSubscriptionRepository;
        $this->_userRepository = $userRepository;
        $this->_subscriptionValidation = $subscriptionValidation;
        $this->_userValidation = $userValidation;
        $this->_eecInstance = $instanceManager->getEecInstance();
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

        return $userEligibility;
    }

    /**
     * Set EEC subscription timestamps.
     */
    public function setUserSubscriptionTimestamps(string $subscriptionType, int $subscriptionId)
    {
        $subscription = self::LONG_SUBSCRIPTION_TYPE === $subscriptionType
            ? $this->_em->getRepository(LongDistanceSubscription::class)->find($subscriptionId)
            : $this->_em->getRepository(ShortDistanceSubscription::class)->find($subscriptionId);

        if (is_null($subscription)) {
            throw new \LogicException('The subscription was not found');
        }

        if (!$this->_subscriptionValidation->isSubscriptionValidForTimestampsProcess($subscription)) {
            throw new \LogicException('Subscription cannot be processed at this time');
        }

        $this->_loggerService->log('Performing the timestamping process');
        $this->setDriver($subscription->getUser());

        $this->_timestampTokenManager->setMissingSubscriptionTimestampTokens($subscription, Log::TYPE_VERIFY);

        $this->_em->flush();

        $response = 'The timestamping process is complete';

        $this->_loggerService->log($response);

        return $response;
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

    public function updateTimestampTokens(User $user): User
    {
        $this->setDriver($user);

        if (!is_null($this->getDriver()->getLongDistanceSubscription())) {
            $this->_timestampTokenManager->setSubscriptionTimestampTokens($this->getDriver()->getLongDistanceSubscription());
        }
        if (!is_null($this->getDriver()->getShortDistanceSubscription())) {
            $this->_timestampTokenManager->setSubscriptionTimestampTokens($this->getDriver()->getShortDistanceSubscription());
        }

        $this->_em->flush();

        return $this->getDriver();
    }

    /**
     * Set missing subscription timestamps.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     *
     * @return bool Returns if getting tokens was successful
     */
    public function setTimestamps($subscription): bool
    {
        $this->setDriver($subscription->getUser());

        $this->_timestampTokenManager->setMissingSubscriptionTimestampTokens($subscription, Log::TYPE_VERIFY);

        $this->_em->flush();

        return false;
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
    public function commitSubscription($subscription, $referenceObject, bool $pushOnlyMode = false): void
    {
        if ($subscription->isCommited()) {
            $stage = new ResetSubscription($this->_em, $subscription);
            $stage->execute();
        }

        $commitClass = $subscription instanceof LongDistanceSubscription
            ? 'App\\Incentive\\Service\\Stage\\CommitLDSubscription'
            : 'App\\Incentive\\Service\\Stage\\CommitSDSubscription';

        $stage = new $commitClass($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $subscription, $referenceObject, $pushOnlyMode);
        $stage->execute();
    }

    public function autoRecommitSubscriptions(): void
    {
        // Processing subscriptions that simply need to be reset
        $sdSubscriptions = SubscriptionProvider::getSubscriptionsCanBeReset($this->_shortDistanceSubscriptionRepository->getSubscriptionsReadyToBeRecommited(), true);
        $ldSubscriptions = SubscriptionProvider::getSubscriptionsCanBeReset($this->_longDistanceSubscriptionRepository->getSubscriptionsReadyToBeRecommited(), true);

        foreach (array_merge($sdSubscriptions, $ldSubscriptions) as $subscription) {
            $this->resetSubscription($subscription);
        }

        // Processing subscriptions that need to be recommit
        $sdSubscriptions = SubscriptionProvider::getSubscriptionsCanBeReset($this->_shortDistanceSubscriptionRepository->getSubscriptionsReadyToBeRecommited());
        $ldSubscriptions = SubscriptionProvider::getSubscriptionsCanBeReset($this->_longDistanceSubscriptionRepository->getSubscriptionsReadyToBeRecommited());

        foreach (array_merge($sdSubscriptions, $ldSubscriptions) as $subscription) {
            $this->recommitSubscription($subscription);
        }
    }

    public function recommitSubscription($subscription): void
    {
        $stage = new AutoRecommitSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $subscription);
        $stage->execute();
    }

    /**
     * STEP 17 - Validate a subscription.
     *
     * @param CarpoolPayment|CarpoolProof $referenceObject
     */
    public function validateSubscription($referenceObject, bool $pushOnlyMode = false): void
    {
        $validateClass = $referenceObject instanceof CarpoolPayment
            ? 'App\\Incentive\\Service\\Stage\\ValidateLDSubscription'
            : 'App\\Incentive\\Service\\Stage\\ProofValidate';

        $stage = new $validateClass($this->_em, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $referenceObject, $pushOnlyMode);
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

        $journeyProvider = new JourneyProvider($this->_longDistanceJourneyRepository);
        $journey = $journeyProvider->getJourneyFromCarpoolProof($carpoolProof);

        if (is_null($journey)) {
            return;
        }

        $stage = new ProofInvalidate($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $journey);
        $stage->execute();
    }

    public function proofsRecover(string $subscriptionType, ?int $userId): void
    {
        if (!is_null($userId)) {
            $user = $this->_em->getRepository(User::class)->find($userId);

            if (is_null($user)) {
                throw new NotFoundHttpException('The requested user was not found');
            }

            $stage = new ProofRecovery($this->_em, $this->_carpoolItemRepository, $this->_carpoolProofRepository, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $user, $subscriptionType);
            $stage->execute();

            return;
        }

        $users = $users = $this->_userRepository->findUsersCeeSubscribed();

        foreach ($users as $user) {
            $stage = new ProofRecovery($this->_em, $this->_carpoolItemRepository, $this->_carpoolProofRepository, $this->_longDistanceJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $user, $subscriptionType);
            $stage->execute();
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
     * STEP 5 - Create a subscription from it's type.
     *
     * @throws \LogicException
     */
    protected function _createSubscription(string $subscriptionType): void
    {
        if (!Subscription::isTypeAllowed($subscriptionType)) {
            throw new \LogicException('eec_subscriptionType_unallowed');
        }

        if (!$this->_instanceManager->{'is'.ucfirst($subscriptionType).'SubscriptionAvailable'}()) {
            throw new \LogicException('eec_subscriptionType_'.$subscriptionType.'_closed');
        }

        if (!is_null($this->getDriver()->{'get'.ucfirst($subscriptionType).'DistanceSubscription'}())) {
            throw new \LogicException('eec_subscriptionType_'.$subscriptionType.'_allready.subscribed');
        }

        if (!$this->isDriverAccountReadyForSubscription($subscriptionType)) {
            throw new \LogicException('eec_subscriptionType_'.$subscriptionType.'_unready');
        }

        $stage = new CreateSubscription($this->_em, $this->_timestampTokenManager, $this->_loggerService, $this->_eecInstance, $this->_driver, $subscriptionType);
        $stage->execute();
    }

    /**
     * STEP 20 - Vérify a subscription from it's ID.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    protected function _verifySubscription($subscription): void
    {
        $stage = new VerifySubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $subscription);
        $stage->execute();
    }
}
