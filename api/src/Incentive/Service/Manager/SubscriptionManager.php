<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Repository\CarpoolProofRepository;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Resource\EecEligibility;
use App\Incentive\Service\Definition\DefinitionSelector;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionManager extends MobConnectManager
{
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_REJECTED = 'REJETEE';
    public const STATUS_VALIDATED = 'VALIDEE';

    public const VERIFICATION_STATUS_PENDING = 0;
    public const VERIFICATION_STATUS_ENDED = 1;

    private $_ceeEligibleProofs = [];

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var LongDistanceSubscriptionRepository
     */
    private $_longDistanceSubscriptionRepository;

    /**
     * @var ShortDistanceSubscriptionRepository
     */
    private $_shortDistanceSubscriptionRepository;

    /**
     * @var CeeSubscriptions
     */
    private $_subscriptions;

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
        JourneyManager $journeyManager,
        TimestampTokenManager $timestampTokenManager,
        CarpoolProofRepository $carpoolProofRepository,
        LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository,
        ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository
    ) {
        parent::__construct($em, $instanceManager, $loggerService);

        $this->_journeyManager = $journeyManager;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_longDistanceSubscriptionRepository = $longDistanceSubscriptionRepository;
        $this->_shortDistanceSubscriptionRepository = $shortDistanceSubscriptionRepository;
        $this->_subscriptionValidation = $subscriptionValidation;
        $this->_userValidation = $userValidation;
    }

    /**
     * Step 5 - Creating incentives requests.
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

        $this->_em->flush();
    }

    /**
     * Returns EEC subscriptions for the authenticated user.
     */
    public function getMyEecSubscriptions(User $driver)
    {
        return new CeeSubscriptions($driver);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     *
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    public function getMobConnectSubscription($subscription)
    {
        $this->setDriver($subscription->getUser());

        return $subscription->setMoBSubscription(json_encode($this->getSubscription($subscription, $this->getDriver())->getContent()));
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

    /**
     * Step 20.
     */
    public function verifySubscriptionFromControllerCommand(?string $subscriptionType, ?string $subscriptionId)
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
            throw new NotFoundHttpException("The {$subscriptionType} subscription was not found");
        }

        return $this->verifySubscription($subscription);
    }

    /**
     * STEP 20 - Verify subscriptions.
     */
    public function verifySubscriptions()
    {
        $shortDistanceSubscriptions = $this->_shortDistanceSubscriptionRepository->getReadyForVerify();
        $longDistanceSubscriptions = $this->_longDistanceSubscriptionRepository->getReadyForVerify();

        $subscriptions = array_merge($shortDistanceSubscriptions, $longDistanceSubscriptions);

        $this->_loggerService->log('There is '.count($subscriptions).' journeys to process');

        foreach ($subscriptions as $key => $subscription) {
            $this->verifySubscription($subscription);
        }

        $this->_loggerService->log('Process processing is complete');
    }

    /**
     * Step 20 - Vérify a subscription.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function verifySubscription($subscription)
    {
        $this->_loggerService->log('Step 20 - Obtaining missing tokens');
        $subscription = $this->_timestampTokenManager->setMissingSubscriptionTimestampTokens($subscription, Log::TYPE_VERIFY);

        if (!$subscription->isReadyToVerify()) {
            $this->_loggerService->log('The subscription '.$subscription->getId().' is not ready for verification');

            if (!$subscription->isAddressValid()) {
                // TODO: notify the user that his residence address must be entered.
            }

            $response = new MobConnectSubscriptionTimestampsResponse([
                'code' => Log::VERIFICATION_VALIDATION_ERROR,
                'content' => Log::ERROR_MESSAGES[Log::VERIFICATION_VALIDATION_ERROR],
            ]);

            $subscription->addLog($response, Log::TYPE_VERIFY);

            $this->_em->flush();

            return $response;
        }

        switch (true) {
            case $subscription instanceof LongDistanceSubscription:
                $this->_loggerService->log('Verification for the long-distance subscription with the ID '.$subscription->getId());

                break;

            case $subscription instanceof ShortDistanceSubscription:
                $this->_loggerService->log('Verification for the short-distance subscription with the ID '.$subscription->getId());

                break;
        }

        $this->_driver = $subscription->getUser();

        $verifyResponse = $this->executeRequestVerifySubscription($subscription->getSubscriptionId());

        if ($this->hasRequestErrorReturned($verifyResponse)) {
            return $verifyResponse;
        }

        $subscription->addLog($verifyResponse, Log::TYPE_VERIFY);

        $subscription->setStatus(
            MobConnectSubscriptionVerifyResponse::SUCCESS_STATUS === $verifyResponse->getCode()
            ? $verifyResponse->getStatus() : self::STATUS_ERROR
        );

        if (self::STATUS_VALIDATED === $subscription->getStatus()) {
            $subscription->setBonusStatus(Subscription::BONUS_STATUS_OK);
            $subscription->setStatus(self::STATUS_VALIDATED);
        } else {
            $subscription->setBonusStatus(Subscription::BONUS_STATUS_NO);
            $subscription->setStatus(self::STATUS_REJECTED);
        }

        $subscription->setVerificationDate();

        $this->_em->flush();

        return $subscription;
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
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function getSubscription($subscription, User $user): MobConnectSubscriptionResponse
    {
        return $this->getSubscription($subscription, $user);
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

    public function getTimestamps()
    {
        return $this->_timestampTokenManager->getCurrentTimestampTokensResponse();
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
     * @return bool|LongDistanceSubscription|ShortDistanceSubscription
     */
    private function _createSubscription(string $subscriptionType)
    {
        if (
            $this->_instanceManager->{'is'.ucfirst($subscriptionType).'SubscriptionAvailable'}()            // The service is available
            && is_null($this->getDriver()->{'get'.ucfirst($subscriptionType).'DistanceSubscription'}())     // Subscription does not yet exist
            && $this->isDriverAccountReadyForSubscription($subscriptionType)                                // There is no incompatibility with the user account
        ) {
            $postResponse = $this->postSubscription($subscriptionType);

            $subscriptionClass = 'App\Incentive\Entity\\'.ucfirst($subscriptionType).'DistanceSubscription';

            if (!$this->hasRequestErrorReturned($postResponse)) {
                $subscription = new $subscriptionClass(
                    $this->getDriver(),
                    $postResponse,
                    DefinitionSelector::getDefinition($subscriptionType)
                );
                $subscription->addLog($postResponse, Log::TYPE_SUBSCRIPTION);

                $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_INCENTIVE);

                $this->_em->persist($subscription);

                return $subscription;
            }
        }

        return false;
    }
}
