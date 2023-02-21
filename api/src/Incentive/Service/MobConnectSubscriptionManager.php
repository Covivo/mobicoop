<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\DataProvider\Entity\OpenIdSsoProvider;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Entity\Flat\LongDistanceSubscription as FlatLongDistanceSubscription;
use App\Incentive\Entity\Flat\ShortDistanceSubscription as FlatShortDistanceSubscription;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\MobConnectAuth;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Resource\CeeSubscriptions;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Subscription Management Manager.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class MobConnectSubscriptionManager
{
    /**
     * @var int
     */
    private $_carpoolProofDeadline;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var LoggerService
     */
    private $_loggerService;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var LongDistanceJourneyRepository
     */
    private $_longDistanceJourneyRepository;

    /**
     * @var ShortDistanceJourneyRepository
     */
    private $_shortDistanceJourneyRepository;

    /**
     * @var MobConnectApiProvider
     */
    private $_mobConnectApiProvider;

    /**
     * @var array
     */
    private $_mobConnectParams;

    /**
     * @var array
     */
    private $_ssoServices;

    /**
     * The authenticated user.
     *
     * @var User
     */
    private $_user;

    private $_userSubscription;

    private $_ceeSubscription;
    private $_ceeEligibleProofs;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        EventDispatcherInterface $eventDispatcher,
        LoggerService $loggerService,
        LoggerInterface $loggerInterface,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        ShortDistanceJourneyRepository $shortDistanceJourneyRepository,
        array $ssoServices,
        array $mobConnectParams,
        int $proofDeadline
    ) {
        $this->_em = $em;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_loggerService = $loggerService;
        $this->_logger = $loggerInterface;
        $this->_longDistanceJourneyRepository = $longDistanceJourneyRepository;
        $this->_shortDistanceJourneyRepository = $shortDistanceJourneyRepository;

        $this->_user = $security->getUser();

        $this->_ssoServices = $ssoServices;
        $this->_mobConnectParams = $mobConnectParams;
        $this->_ceeEligibleProofs = [];
        $this->_carpoolProofDeadline = $proofDeadline;
    }

    private function __createAuth(User $user, SsoUser $ssoUser)
    {
        $mobConnectAuth = new MobConnectAuth($user, $ssoUser);

        $this->_user->setMobConnectAuth($mobConnectAuth);

        $this->_em->persist($mobConnectAuth);
        $this->_em->flush();
    }

    private function __updateAuth(SsoUser $ssoUser)
    {
        $mobConnectAuth = $this->_user->getMobConnectAuth();

        $mobConnectAuth->setAccessToken($ssoUser->getAccessToken());
        $mobConnectAuth->setAccessTokenExpiresDate($ssoUser->getAccessTokenExpiresDuration());
        $mobConnectAuth->setRefreshToken($ssoUser->getRefreshToken());
        $mobConnectAuth->setRefreshTokenExpiresDate($ssoUser->getRefreshTokenExpiresDuration());

        $this->_em->flush();
    }

    private function __getCarpoolersNumber(int $askId): int
    {
        $conn = $this->_em->getConnection();

        $sql = 'SELECT DISTINCT ci.debtor_user_id FROM carpool_item ci WHERE ci.ask_id = '.$askId.'';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return count($stmt->fetchAll(\PDO::FETCH_COLUMN)) + 1;
    }

    private function __getFlatJourneys($journeys): array
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

    private function __getRpcJourneyId(int $id): string
    {
        return 'Mobicoop_'.$id;
    }

    private function __getSubscriptionId(): string
    {
        return $this->_userSubscription->getSubscriptionId();
    }

    private function __isValidParameters(): bool
    {
        return
                !empty($this->_ssoServices)
                && array_key_exists(MobConnectApiProvider::SERVICE_NAME, $this->_ssoServices)

            && (
                !empty($this->_mobConnectParams)
                && (
                    array_key_exists('api_uri', $this->_mobConnectParams)
                    && !is_null($this->_mobConnectParams['api_uri'])
                    && !empty($this->_mobConnectParams['api_uri'])
                )
                && (
                    array_key_exists('credentials', $this->_mobConnectParams)
                    && is_array($this->_mobConnectParams['credentials'])
                    && !empty($this->_mobConnectParams['credentials'])
                    && array_key_exists('client_id', $this->_mobConnectParams['credentials'])
                    && !empty($this->_mobConnectParams['credentials']['client_id'])
                    && array_key_exists('api_key', $this->_mobConnectParams['credentials'])
                )
                && (
                    array_key_exists('subscription_ids', $this->_mobConnectParams)
                    && is_array($this->_mobConnectParams['subscription_ids'])
                    && !empty($this->_mobConnectParams['subscription_ids'])
                    && array_key_exists('short_distance', $this->_mobConnectParams['subscription_ids'])
                    && !empty($this->_mobConnectParams['subscription_ids']['short_distance'])
                    && array_key_exists('long_distance', $this->_mobConnectParams['subscription_ids'])
                    && !empty($this->_mobConnectParams['subscription_ids']['long_distance'])
                )
            )
        ;
    }

    private function __setApiProviderParams()
    {
        $this->_mobConnectApiProvider = new MobConnectApiProvider($this->_em, new MobConnectApiParams($this->_mobConnectParams), $this->_loggerService, $this->_user, $this->_ssoServices);
    }

    private function __verifySubscription(): MobConnectSubscriptionVerifyResponse
    {
        $this->__setApiProviderParams();

        $response = $this->_mobConnectApiProvider->verifyUserSubscription($this->__getSubscriptionId());

        if (!in_array($response->getCode(), MobConnectResponse::ERROR_CODES)) {
            $this->_userSubscription->setStatus($response->getStatus());
            $this->_userSubscription->setRejectionReason($response->getRejectReason());
            $this->_userSubscription->setComment($response->getComment());
            $this->_userSubscription->setVerificationDate();
            $this->_userSubscription->setLastTimestamp($response->getTimestamp());
        }

        return $response;
    }

    /**
     * Keep only the eligible proofs (for short distance only).
     */
    private function __getCEEEligibleProofsShortDistance(User $user)
    {
        foreach ($user->getCarpoolProofsAsDriver() as $proof) {
            if (!is_null($proof->getAsk()) && $proof->getAsk()->getMatching()->getCommonDistance() >= CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS) {
                continue;
            }

            if (CarpoolProof::TYPE_HIGH !== $proof->getType() && CarpoolProof::TYPE_UNDETERMINED_DYNAMIC !== $proof->getType()) {
                continue;
            }

            $this->_ceeEligibleProofs[] = $proof;
        }
    }

    private function __computeShortDistance(User $user)
    {
        $this->__getCEEEligibleProofsShortDistance($user);
        foreach ($this->_ceeEligibleProofs as $proof) {
            switch ($proof->getStatus()) {
                case CarpoolProof::STATUS_PENDING:
                case CarpoolProof::STATUS_SENT:$this->_ceeSubscription->setNbPendingProofs($this->_ceeSubscription->getNbPendingProofs() + 1);

                    break;

                case CarpoolProof::STATUS_ERROR:
                case CarpoolProof::STATUS_ACQUISITION_ERROR:
                case CarpoolProof::STATUS_NORMALIZATION_ERROR:
                case CarpoolProof::STATUS_FRAUD_ERROR:$this->_ceeSubscription->setNbRejectedProofs($this->_ceeSubscription->getNbRejectedProofs() + 1);

                    break;

                case CarpoolProof::STATUS_VALIDATED:$this->_ceeSubscription->setNbValidatedProofs($this->_ceeSubscription->getNbValidatedProofs() + 1);

                    break;
            }
        }
    }

    /**
     * Create or update a moBConnect journey.
     */
    private function __associateJourneyToSubscription(array $journeys, CarpoolProof $carpoolProof, CarpoolPayment $carpoolPayment = null)
    {
        $filteredJourneys = array_filter($journeys, function ($journey) use ($carpoolProof) {
            return $journey->getCarpoolProof() === $carpoolProof;
        });

        switch (is_null($carpoolPayment)) {
            case true:
                if (empty($filteredJourneys)) {
                    $journey = new ShortDistanceJourney(
                        $carpoolProof,
                        $this->__getCarpoolersNumber($carpoolProof->getAsk()->getId()),
                        $this->__getRpcJourneyId($carpoolProof->getId()),
                        CeeJourneyService::RPC_NUMBER_STATUS
                    );

                    $this->_userSubscription->addShortDistanceJourney($journey);
                } else {
                    $journey = $filteredJourneys[0];
                    $journey->setCarpoolersNumber($this->__getCarpoolersNumber($carpoolProof->getAsk()->getId()));
                    $journey->setRpcJourneyId($this->__getRpcJourneyId($carpoolProof->getId()));
                    $journey->setRpcNumberStatus(CeeJourneyService::RPC_NUMBER_STATUS);
                }

                break;

            default:
                if (empty($filteredJourneys)) {
                    $journey = new LongDistanceJourney(
                        $carpoolPayment,
                        $carpoolProof,
                        $this->__getCarpoolersNumber($carpoolProof->getAsk()->getId())
                    );

                    $this->_userSubscription->addLongDistanceJourney($journey);
                } else {
                    $journey = $filteredJourneys[0];
                    $journey->setCarpoolersNumber($this->__getCarpoolersNumber($carpoolProof->getAsk()->getId()));
                }

                break;
        }

        return $journey;
    }

    // * PUBLIC FUNCTIONS ---------------------------------------------------------------------------------------------------------------------------

    public function updateAuth(User $user, SsoUser $ssoUser)
    {
        $this->_user = $user;

        if (is_null($this->_user->getMobConnectAuth())) {
            $this->__createAuth($this->_user, $ssoUser);
            $this->_loggerService->log('The mobConnectAuth entity for user '.$user->getId().' has been created');
        } else {
            $this->__updateAuth($ssoUser);
            $this->_loggerService->log('The mobConnectAuth entity for user '.$user->getId().' has been updated');
        }

        $this->_em->flush();
    }

    /**
     * For the authenticated user, if needed, creates the CEE sheets.
     */
    public function createSubscriptions(User $user)
    {
        if (!$this->__isValidParameters()) {
            return;
        }

        $this->_loggerService->log('The creating subscriptions for the user '.$user->getId().' begins');

        $this->_user = $user;

        if (is_null($this->_user->getShortDistanceSubscription())) {
            $shortDistanceSubscription = $this->createShortDistanceSubscription();

            if (!is_null($shortDistanceSubscription)) {
                $this->_em->persist($shortDistanceSubscription);
                $this->_loggerService->log('The short distance subscription has been declared and created');
            } else {
                $this->_loggerService->log('The short distance subscription has not been created');
            }
        }

        if (is_null($this->_user->getLongDistanceSubscription())) {
            $longDistanceSubscription = $this->createLongDistanceSubscription();

            if (!is_null($longDistanceSubscription)) {
                $this->_em->persist($longDistanceSubscription);
                $this->_loggerService->log('The long distance subscription has been declared and created');
            } else {
                $this->_loggerService->log('The long distance subscription has not been created');
            }
        }

        $this->_em->flush();

        $this->_loggerService->log('The process of creating subscriptions for the user '.$user->getId().' is complete');
    }

    public function createShortDistanceSubscription()
    {
        $this->__setApiProviderParams();

        if (is_null($this->_user->getShortDistanceSubscription()) && CeeJourneyService::isUserAccountReadyForShortDistanceSubscription($this->_user, $this->_logger)) {
            $mobConnectShortDistanceSubscription = $this->_mobConnectApiProvider->postSubscriptionForShortDistance();

            return new ShortDistanceSubscription($this->_user, $mobConnectShortDistanceSubscription);
        }

        return null;
    }

    public function createLongDistanceSubscription()
    {
        $this->__setApiProviderParams();

        if (is_null($this->_user->getLongDistanceSubscription()) && CeeJourneyService::isUserAccountReadyForLongDistanceSubscription($this->_user, $this->_logger)) {
            $mobConnectLongDistanceSubscription = $this->_mobConnectApiProvider->postSubscriptionForLongDistance();

            return new LongDistanceSubscription($this->_user, $mobConnectLongDistanceSubscription);
        }

        return null;
    }

    /**
     * Returns flat paths to be used in particular as logs.
     * This service is called by the CeeSubscriptionsCollectionDataProvider.
     */
    public function getUserSubscriptions(User $user)
    {
        $this->_ceeSubscription = new CeeSubscriptions($this->_user->getId());

        if (!is_null($user->getShortDistanceSubscription())) {
            $shortDistanceSubscriptions = $this->__getFlatJourneys($user->getShortDistanceSubscription()->getShortDistanceJourneys());
            $this->_ceeSubscription->setShortDistanceSubscriptions($shortDistanceSubscriptions);
        }

        if (!is_null($user->getLongDistanceSubscription())) {
            $longDistanceSubscriptions = $this->__getFlatJourneys($user->getLongDistanceSubscription()->getLongDistanceJourneys());

            $this->_ceeSubscription->setLongDistanceSubscriptions($longDistanceSubscriptions);
        }

        $this->__computeShortDistance($user);

        return [$this->_ceeSubscription];
    }

    /**
     * Updates subscriptions (long or short distance) based on provided carpoolProof.
     */
    public function updateSubscription(CarpoolProof $carpoolProof, CarpoolPayment $carpoolPayment = null): void
    {
        if (!$this->__isValidParameters()) {
            return;
        }

        $this->_loggerService->log('The proof '.$carpoolProof->getId().' processing process begins');

        $this->_user = $carpoolProof->getDriver();

        switch (true) {
            case CeeJourneyService::isValidLongDistanceJourney($carpoolProof, $this->_logger):
                $this->_loggerService->log('The journey successfully passed the long distance test');
                $this->_userSubscription = $this->_user->getLongDistanceSubscription();

                if (
                    is_null($this->_userSubscription)
                    || CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD <= count($this->_userSubscription->getLongDistanceJourneys())
                ) {
                    if (is_null($this->_userSubscription)) {
                        $this->_loggerService->log('The user does not have a subscription for long distance journeys', 'alert');
                    }
                    if (!is_null($this->_userSubscription) && CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD <= count($this->_userSubscription->getLongDistanceJourneys())) {
                        $this->_loggerService->log('The number of declared long-distance journeys has already reached its maximum', 'alert');
                    }

                    return;
                }

                /**
                 * @var LongDistanceJourney
                 */
                $journey = $this->__associateJourneyToSubscription(
                    $this->_userSubscription->getLongDistanceJourneys()->toArray(),
                    $carpoolProof,
                    $carpoolPayment
                );

                break;

            case CeeJourneyService::isValidShortDistanceJourney($carpoolProof, $this->_logger):
                $this->_loggerService->log('The journey successfully passed the short distance test');
                $this->_userSubscription = $this->_user->getShortDistanceSubscription();

                if (
                    is_null($this->_userSubscription)
                    || CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD <= count($this->_userSubscription->getShortDistanceJourneys())
                ) {
                    if (is_null($this->_userSubscription)) {
                        $this->_loggerService->log('The user does not have a subscription for short distance journeys', 'alert');
                    }
                    if (!is_null($this->_userSubscription) && CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD <= count($this->_userSubscription->getShortDistanceJourneys())) {
                        $this->_loggerService->log('The number of declared short-distance journeys has already reached its maximum', 'alert');
                    }

                    return;
                }

                /**
                 * @var ShortDistanceJourney
                 */
                $journey = $this->__associateJourneyToSubscription($this->_userSubscription->getShortDistanceJourneys()->toArray(), $carpoolProof);

                break;

            default:
                $this->_loggerService->log('The trip failed the short and long distance tests');

                break;
        }

        $paymentDate = !is_null($carpoolPayment) && !is_null($carpoolPayment->getUpdatedDate()) ? $carpoolPayment->getUpdatedDate() : null;

        if (isset($journey) && $this->_userSubscription) {
            $this->__setApiProviderParams();

            switch (true) {
                case $this->_userSubscription instanceof LongDistanceSubscription:
                    $this->_loggerService->log('Action processing for long distance subscriptions');

                    $longDistanceJourneysNumber = count($this->_userSubscription->getLongDistanceJourneys());

                    switch ($longDistanceJourneysNumber) {
                        case CeeJourneyService::LOW_THRESHOLD_PROOF:
                            $this->_loggerService->log('The journey is the first');
                            // The journey is added to the EEC sheet
                            if (is_null($paymentDate)) {
                                $this->_loggerService->log(MobConnectMessages::PAYMENT_DATE_MISSING, 'alert');

                                throw new \LogicException(MobConnectMessages::PAYMENT_DATE_MISSING);
                            }

                            $mobConnectResponse = $this->_mobConnectApiProvider->patchUserSubscription($this->__getSubscriptionId(), null, false, $paymentDate);
                            $journey->setHttpRequestStatus($mobConnectResponse->getCode());
                            $journey->setRank(CeeJourneyService::LOW_THRESHOLD_PROOF);

                            $this->_em->flush();

                            break;

                        case CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD:
                            $this->_loggerService->log('The journey is the last ('.CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD.')');
                            $journey->setRank(CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD);

                            $this->_em->flush();

                            break;

                        default:
                            if (CeeJourneyService::LOW_THRESHOLD_PROOF < $longDistanceJourneysNumber && $longDistanceJourneysNumber < CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD) {
                                $this->_loggerService->log('Treatment for a number of trips equal to '.$longDistanceJourneysNumber);
                                $journey->setRank($longDistanceJourneysNumber);
                                $this->_em->flush();
                            } else {
                                $this->_loggerService->log('No treatment-The current number of journeys is greater than the maximum threshold');
                            }

                            break;
                    }

                    break;

                case $this->_userSubscription instanceof ShortDistanceSubscription:
                    $this->_loggerService->log('Action processing for short distance subscriptions');

                    $shortDistanceJourneysNumber = count($this->_userSubscription->getShortDistanceJourneys());

                    switch ($shortDistanceJourneysNumber) {
                        case CeeJourneyService::LOW_THRESHOLD_PROOF:
                            $this->_loggerService->log('The journey is the first');

                            // The journey is added to the EEC sheet
                            $mobConnectResponse = $this->_mobConnectApiProvider->patchUserSubscription($this->__getSubscriptionId(), $this->__getRpcJourneyId($carpoolProof->getId()), true);
                            $journey->setHttpRequestStatus($mobConnectResponse->getCode());
                            $journey->setRank(CeeJourneyService::LOW_THRESHOLD_PROOF);

                            $this->_em->flush();

                            break;

                        case CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD:
                            $this->_loggerService->log('The journey is the last ('.CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD.')');
                            $journey->setBonusStatus(CeeJourneyService::BONUS_STATUS_PENDING);
                            $journey->setRank(CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD);

                            $this->_em->flush();

                            break;

                        default:
                            if (
                                CeeJourneyService::LOW_THRESHOLD_PROOF < $shortDistanceJourneysNumber
                                && $shortDistanceJourneysNumber < CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD
                            ) {
                                $this->_loggerService->log('Treatment for a number of trips equal to '.$shortDistanceJourneysNumber);
                                $journey->setRank($shortDistanceJourneysNumber);
                                $this->_em->flush();
                            } else {
                                $this->_loggerService->log('No treatment-The current number of journeys is greater than the maximum threshold');
                            }

                            $this->_em->flush();

                            break;
                    }

                    break;

                default:
                    $this->_loggerService->log('Unknown subscription type', 'alert');

                    break;
            }
        }

        $this->_loggerService->log('The proof '.$carpoolProof->getId().' processing process is complete');
    }

    /**
     * Updates long distance subscription after a payment has been validated.
     */
    public function updateLongDistanceSubscriptionAfterPayment(CarpoolPayment $carpoolPayment): void
    {
        if (!$this->__isValidParameters()) {
            return;
        }

        // Array of carpoolItem where driver is associated with MobConnect
        $filteredCarpoolItems = array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver->getSsoId())
                && !is_null($driver->getSsoProvider())
                && OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECT === $driver->getSsoProvider()
            ;
        });

        foreach ($filteredCarpoolItems as $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            // Array of carpoolProof where driver is the carpoolItem driver
            $filteredCarpoolProofs = array_filter($carpoolItem->getAsk()->getCarpoolProofs(), function (CarpoolProof $carpoolProof) use ($driver) {
                return $carpoolProof->getDriver() === $driver;
            });

            foreach ($filteredCarpoolProofs as $carpool) {
                $this->updateSubscription($carpool, $carpoolPayment);
            }
        }
    }

    /**
     * Undocumented function.
     */
    public function verifyJourneys()
    {
        $this->_loggerService->log('Process processing begins');
        $this->_loggerService->log('Obtaining eligible short-distance journeys');
        $shortDistanceJourneys = $this->_shortDistanceJourneyRepository->getReadyForVerify();

        $this->_loggerService->log('Obtaining eligible long-distance journeys');
        $longDistanceJourneys = $this->_longDistanceJourneyRepository->getReadyForVerify();

        $journeys = array_merge($shortDistanceJourneys, $longDistanceJourneys);

        $this->_loggerService->log('There is '.count($journeys).' journeys to process');

        foreach ($journeys as $key => $journey) {
            switch (true) {
                case $journey instanceof LongDistanceJourney:
                    $this->_loggerService->log('Verification for the long-distance journey with the ID '.$journey->getId());

                    break;

                case $journey instanceof ShortDistanceJourney:
                    $this->_loggerService->log('Verification for the short-distance journey with the ID '.$journey->getId());

                    break;
            }

            $this->_userSubscription = $journey->getSubscription();

            $this->_user = $this->_userSubscription->getUser();

            $response = $this->__verifySubscription();

            if (!is_null($this->_userSubscription) && CeeJourneyService::STATUS_VALIDATED === $this->_userSubscription->getStatus()) {
                $journey->setBonusStatus(CeeJourneyService::BONUS_STATUS_OK);
                $journey->getSubscription()->setStatus(CeeJourneyService::STATUS_VALIDATED);
            } else {
                $journey->setBonusStatus(CeeJourneyService::BONUS_STATUS_NO);
                $journey->getSubscription()->setStatus(CeeJourneyService::STATUS_REJECTED);
            }

            $journey->setHttpRequestStatus($response->getCode());
            $journey->setVerificationStatus(CeeJourneyService::VERIFICATION_STATUS_ENDED);
        }

        $this->_em->flush();
        $this->_loggerService->log('Process processing is complete');
    }
}
