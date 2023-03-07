<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponse;
use App\Incentive\Entity\Flat\LongDistanceSubscription as FlatLongDistanceSubscription;
use App\Incentive\Entity\Flat\ShortDistanceSubscription as FlatShortDistanceSubscription;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\UserValidation;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionManager extends MobConnectManager
{
    public const BONUS_STATUS_PENDING = 0;
    public const BONUS_STATUS_NO = 1;
    public const BONUS_STATUS_OK = 2;

    public const STATUS_REJECTED = 'REJETEE';
    public const STATUS_VALIDATED = 'VALIDEE';

    public const VERIFICATION_STATUS_PENDING = 0;
    public const VERIFICATION_STATUS_ENDED = 1;

    private $_ceeEligibleProofs = [];

    /**
     * @var LongDistanceJourneyRepository
     */
    private $_longDistanceJourneyRepository;

    /**
     * @var ShortDistanceJourneyRepository
     */
    private $_shortDistanceJourneyRepository;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_subscription;

    /**
     * @var CeeSubscriptions
     */
    private $_subscriptions;

    /**
     * @var UserValidation
     */
    private $_userValidation;

    public function __construct(
        EntityManagerInterface $em,
        UserValidation $userValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        ShortDistanceJourneyRepository $shortDistanceJourneyRepository,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_longDistanceJourneyRepository = $longDistanceJourneyRepository;
        $this->_shortDistanceJourneyRepository = $shortDistanceJourneyRepository;
        $this->_userValidation = $userValidation;
    }

    /**
     * For the authenticated user, if needed, creates the CEE sheets.
     */
    public function createSubscriptions(User $driver)
    {
        if (!$this->isValidParameters()) {
            return;
        }

        $this->_driver = $driver;

        if (
            is_null($this->_driver->getLongDistanceSubscription())
            && $this->_userValidation->isUserAccountReadyForSubscription($this->_driver)
        ) {
            $response = $this->postSubscription();

            $longDistanceSubscription = new LongDistanceSubscription($this->_driver, $response);

            $this->_em->persist($longDistanceSubscription);
        }

        if (
            is_null($this->_driver->getShortDistanceSubscription())
            && $this->_userValidation->isUserAccountReadyForSubscription($this->_driver, false)
        ) {
            $response = $this->postSubscription(false);

            $shortDistanceSubscription = new ShortDistanceSubscription($this->_driver, $response);

            $this->_em->persist($shortDistanceSubscription);
        }

        $this->_em->flush();
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
            $shortDistanceSubscriptions = $this->_getFlatJourneys($shortDistanceSubscription->getShortDistanceJourneys());
            $this->_subscriptions->setShortDistanceSubscriptions($shortDistanceSubscriptions);
        }

        $longDistanceSubscription = $this->_driver->getLongDistanceSubscription();

        if (!is_null($longDistanceSubscription)) {
            $longDistanceSubscriptions = $this->_getFlatJourneys($longDistanceSubscription->getLongDistanceJourneys());

            $this->_subscriptions->setLongDistanceSubscriptions($longDistanceSubscriptions);
        }

        $this->_computeShortDistance();

        return [$this->_subscriptions];
    }

    /**
     * Verify subscriptions.
     */
    public function verifySubscriptions()
    {
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

            $this->_subscription = $journey->getSubscription();

            if (is_null($this->_subscription)) {
                continue;
            }

            $this->_driver = $this->_subscription->getUser();

            $response = $this->verifySubscription($this->_subscription->getSubscriptionId());

            if (!in_array($response->getCode(), MobConnectResponse::ERROR_CODES)) {
                $this->_subscription->setStatus($response->getStatus());

                if (self::STATUS_VALIDATED === $this->_subscription->getStatus()) {
                    $this->_subscription->setBonusStatus(self::BONUS_STATUS_OK);
                    $this->_subscription->setStatus(self::STATUS_VALIDATED);
                } else {
                    $this->_subscription->setBonusStatus(self::BONUS_STATUS_NO);
                    $this->_subscription->setStatus(self::STATUS_REJECTED);
                }

                $this->_subscription->setVerificationDate();
            }
        }

        $this->_em->flush();
        $this->_loggerService->log('Process processing is complete');
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
