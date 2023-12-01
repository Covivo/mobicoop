<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\CarpoolProofRepository;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\Incentive\Entity\Flat\LongDistanceSubscription as FlatLongDistanceSubscription;
use App\Incentive\Entity\Flat\ShortDistanceSubscription as FlatShortDistanceSubscription;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Resource\EecEligibility;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionManager extends MobConnectManager
{
    public const BONUS_STATUS_PENDING = 0;
    public const BONUS_STATUS_NO = 1;
    public const BONUS_STATUS_OK = 2;

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
     * @var TimestampTokenManager
     */
    private $_timestampTokenManager;

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
        HonourCertificateService $honourCertificateService,
        JourneyManager $journeyManager,
        TimestampTokenManager $timestampTokenManager,
        CarpoolProofRepository $carpoolProofRepository,
        LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository,
        ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

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
        if (!$this->isValidParameters()) {
            return;
        }

        $this->setDriver($user);

        if (
            is_null($this->getDriver()->getLongDistanceSubscription())
            && $this->isDriverAccountReadyForSubscription(LongDistanceSubscription::SUBSCRIPTION_TYPE)
        ) {
            $postResponse = $this->postSubscription();

            if (!$this->hasRequestErrorReturned($postResponse)) {
                $longDistanceSubscription = new LongDistanceSubscription($this->getDriver(), $postResponse);
                $longDistanceSubscription->addLog($postResponse, Log::TYPE_SUBSCRIPTION);

                $longDistanceSubscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($longDistanceSubscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_INCENTIVE);

                $this->_em->persist($longDistanceSubscription);
            }
        }

        if (
            is_null($this->getDriver()->getShortDistanceSubscription())
            && $this->isDriverAccountReadyForSubscription(ShortDistanceSubscription::SUBSCRIPTION_TYPE)
        ) {
            $postResponse = $this->postSubscription(false);

            if (!$this->hasRequestErrorReturned($postResponse)) {
                $shortDistanceSubscription = new ShortDistanceSubscription($this->getDriver(), $postResponse);
                $shortDistanceSubscription->addLog($postResponse, Log::TYPE_SUBSCRIPTION);

                $shortDistanceSubscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($shortDistanceSubscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_INCENTIVE);

                $this->_em->persist($shortDistanceSubscription);
            }
        }

        $this->_em->flush();
    }

    /**
     * Set, for a user the mobConnect subscription data.
     */
    public function getUserMobConnectSubscription(User $user): User
    {
        if (!is_null($user->getLongDistanceSubscription())) {
            $user->setLongDistanceSubscription($this->getMobConnectSubscription($user->getLongDistanceSubscription()));
        }

        if (!is_null($user->getShortDistanceSubscription())) {
            $user->setShortDistanceSubscription($this->getMobConnectSubscription($user->getShortDistanceSubscription()));
        }

        return $user;
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     *
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    public function getMobConnectSubscription($subscription)
    {
        $this->setDriver($subscription->getUser());

        return $subscription->setMoBSubscription(json_encode($this->getMobSubscription($subscription->getSubscriptionid())->getContent()));
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
     * Returns flat paths to be used in particular as logs.
     * This service is called by the CeeSubscriptionsCollectionDataProvider.
     */
    public function getUserSubscriptions(User $driver)
    {
        $this->setDriver($driver);

        $this->_subscriptions = new CeeSubscriptions($this->_driver->getId());

        $shortDistanceSubscription = $this->_driver->getShortDistanceSubscription();

        if (!is_null($shortDistanceSubscription)) {
            $shortDistanceSubscription->setVersion();
            $this->_subscriptions->setShortDistanceSubscription($shortDistanceSubscription);

            $shortDistanceSubscriptions = $this->_getFlatJourneys($shortDistanceSubscription->getCompliantJourneys());

            $this->_subscriptions->setShortDistanceSubscriptions($shortDistanceSubscriptions);
            $this->_subscriptions->setShortDistanceExpirationDate($shortDistanceSubscription->getExpirationDate());
        }

        $longDistanceSubscription = $this->_driver->getLongDistanceSubscription();
        if (!is_null($longDistanceSubscription)) {
            $longDistanceSubscription->setVersion();
            $this->_subscriptions->setLongDistanceSubscription($longDistanceSubscription);

            $longDistanceSubscriptions = $this->_getFlatJourneys($longDistanceSubscription->getCompliantJourneys());

            $this->_subscriptions->setLongDistanceSubscriptions($longDistanceSubscriptions);
            $this->_subscriptions->setLongDistanceExpirationDate($longDistanceSubscription->getExpirationDate());
        }

        $this->_em->flush();

        $this->_computeShortDistance();

        return [$this->_subscriptions];
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
            $subscription->setBonusStatus(self::BONUS_STATUS_OK);
            $subscription->setStatus(self::STATUS_VALIDATED);
        } else {
            $subscription->setBonusStatus(self::BONUS_STATUS_NO);
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

    public function getSubscription(string $subscriptionId): MobConnectSubscriptionResponse
    {
        return $this->getMobSubscription($subscriptionId);
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

    private function _computeShortDistance()
    {
        $this->_getCEEEligibleProofsShortDistance();

        foreach ($this->_ceeEligibleProofs as $proof) {
            switch ($proof->getStatus()) {
                case CarpoolProof::STATUS_PENDING:
                case CarpoolProof::STATUS_SENT:$this->_subscriptions->setNbPendingProofs($this->_subscriptions->getNbPendingProofs() + 1);

                    break;

                case CarpoolProof::STATUS_ERROR:
                case CarpoolProof::STATUS_ACQUISITION_ERROR:
                case CarpoolProof::STATUS_NORMALIZATION_ERROR:
                case CarpoolProof::STATUS_FRAUD_ERROR:$this->_subscriptions->setNbRejectedProofs($this->_subscriptions->getNbRejectedProofs() + 1);

                    break;

                case CarpoolProof::STATUS_VALIDATED:$this->_subscriptions->setNbValidatedProofs($this->_subscriptions->getNbValidatedProofs() + 1);

                    break;
            }
        }
    }

    /**
     * Keep only the eligible proofs (for short distance only).
     */
    private function _getCEEEligibleProofsShortDistance()
    {
        foreach ($this->_driver->getCarpoolProofsAsDriver() as $proof) {
            if (
                !is_null($proof->getAsk())
                && !is_null($proof->getAsk()->getMatching())
                && $proof->getAsk()->getMatching()->getCommonDistance() >= CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS
            ) {
                continue;
            }

            if (CarpoolProof::TYPE_HIGH !== $proof->getType() && CarpoolProof::TYPE_UNDETERMINED_DYNAMIC !== $proof->getType()) {
                continue;
            }

            $this->_ceeEligibleProofs[] = $proof;
        }
    }

    private function _getFlatJourneys($journeys): array
    {
        $subscriptions = [];

        foreach ($journeys as $journey) {
            if ($journey instanceof ShortDistanceJourney) {
                array_push($subscriptions, new FlatShortDistanceSubscription($journey));
            } else {
                array_push($subscriptions, new FlatLongDistanceSubscription($journey));
            }
        }

        return $subscriptions;
    }
}
