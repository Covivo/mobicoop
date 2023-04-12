<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\Flat\LongDistanceSubscription as FlatLongDistanceSubscription;
use App\Incentive\Entity\Flat\ShortDistanceSubscription as FlatShortDistanceSubscription;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
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

    public const STATUS_ERROR = 'ERROR';
    public const STATUS_REJECTED = 'REJETEE';
    public const STATUS_VALIDATED = 'VALIDEE';

    public const VERIFICATION_STATUS_PENDING = 0;
    public const VERIFICATION_STATUS_ENDED = 1;

    private $_ceeEligibleProofs = [];

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
     * @var UserValidation
     */
    private $_userValidation;

    public function __construct(
        EntityManagerInterface $em,
        UserValidation $userValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository,
        ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_longDistanceSubscriptionRepository = $longDistanceSubscriptionRepository;
        $this->_shortDistanceSubscriptionRepository = $shortDistanceSubscriptionRepository;
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
        $shortDistanceSubscriptions = $this->_shortDistanceSubscriptionRepository->getReadyForVerify();
        $longDistanceSubscriptions = $this->_longDistanceSubscriptionRepository->getReadyForVerify();

        $subscriptions = array_merge($shortDistanceSubscriptions, $longDistanceSubscriptions);

        $this->_loggerService->log('There is '.count($subscriptions).' journeys to process');

        foreach ($subscriptions as $key => $subscription) {
            switch (true) {
                case $subscription instanceof LongDistanceSubscription:
                    $this->_loggerService->log('Verification for the long-distance subscription with the ID '.$subscription->getId());

                    break;

                case $subscription instanceof ShortDistanceSubscription:
                    $this->_loggerService->log('Verification for the short-distance subscription with the ID '.$subscription->getId());

                    break;
            }

            $this->_driver = $subscription->getUser();

            $response = $this->verifySubscription($subscription->getSubscriptionId());

            $subscription->setStatus(!is_null($response->getStatus()) ? $response->getStatus() : self::STATUS_ERROR);

            if (self::STATUS_VALIDATED === $subscription->getStatus()) {
                $subscription->setBonusStatus(self::BONUS_STATUS_OK);
                $subscription->setStatus(self::STATUS_VALIDATED);
            } else {
                $subscription->setBonusStatus(self::BONUS_STATUS_NO);
                $subscription->setStatus(self::STATUS_REJECTED);
            }

            $subscription->setVerificationDate();
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
