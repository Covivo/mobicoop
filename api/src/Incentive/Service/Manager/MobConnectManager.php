<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Entity\MobConnect\Response\IncentiveResponse;
use App\DataProvider\Entity\MobConnect\Response\IncentivesResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponseInterface;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\UserValidation;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class MobConnectManager
{
    /**
     * Period, expressed in months, preceding the subscription request during which the user must not have made a trip.
     *
     * @var int
     */
    public const WAITING_PERIOD = 3;     // Expressed in months

    public const LONG_SUBSCRIPTION_TYPE = 'long';
    public const SHORT_SUBSCRIPTION_TYPE = 'short';

    public const DRIVER = 1;
    public const PASSENGER = 2;

    public const ALLOWED_SUBSCRIPTION_TYPES = [self::LONG_SUBSCRIPTION_TYPE, self::SHORT_SUBSCRIPTION_TYPE];

    protected const DATE_FORMAT = 'Y-m-d';

    /**
     * @var User
     */
    protected $_driver;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var LongDistanceJourneyRepository
     */
    protected $_longDistanceJourneyRepository;

    /**
     * @var ShortDistanceJourneyRepository
     */
    protected $_shortDistanceJourneyRepository;

    /**
     * @var LoggerService
     */
    protected $_loggerService;

    /**
     * @var HonourCertificateService
     */
    protected $_honourCertificateService;

    /**
     * @var int
     */
    protected $_carpoolProofPrefix;

    /**
     * @var TimestampTokenManager
     */
    protected $_timestampTokenManager;

    /**
     * @var UserValidation
     */
    protected $_userValidation;

    /**
     * @var null|CarpoolItem
     */
    protected $_currentCarpoolItem;

    /**
     * @var null|CarpoolPayment
     */
    protected $_currentCarpoolPayment;

    /**
     * @var null|CarpoolProof
     */
    protected $_currentCarpoolProof;

    /**
     * @var Proposal
     */
    protected $_currentProposal;

    /**
     * @var null|LongDistanceSubscription|ShortDistanceSubscription
     */
    protected $_currentSubscription;

    /**
     * @var InstanceManager
     */
    protected $_instanceManager;

    /**
     * @var bool
     */
    protected $_pushOnlyMode = false;

    /**
     * @var MobConnectApiProvider
     */
    private $_apiProvider;

    /**
     * @var array
     */
    private $_mobConnectParams;

    /**
     * @var array
     */
    private $_ssoServices;

    public function __construct(
        EntityManagerInterface $em,
        InstanceManager $instanceManager,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        $this->_em = $em;

        $this->_instanceManager = $instanceManager;

        $this->_loggerService = $loggerService;
        $this->_honourCertificateService = $honourCertificateService;

        $this->_carpoolProofPrefix = $carpoolProofPrefix;
        $this->_mobConnectParams = $mobConnectParams;
        $this->_ssoServices = $ssoServices;
    }

    public function getHonorCertificate(bool $isLongDistance = true): string
    {
        return $this->_honourCertificateService->generateHonourCertificate($isLongDistance);
    }

    public function setDriver(User $driver): self
    {
        $this->_driver = $driver;

        if (!is_null($this->_driver)) {
            $this->_honourCertificateService->setDriver($this->getDriver());
        }

        return $this;
    }

    public function getCarpoolProofsFromCarpoolItem(CarpoolItem $carpoolItem): array
    {
        $ask = $carpoolItem->getAsk();
        $driver = $carpoolItem->getCreditorUser();

        return !is_null($ask)
            ? array_values(array_filter($ask->getCarpoolProofs(), function ($carpoolProof) use ($driver) {
                return $carpoolProof->getDriver()->getId() === $driver->getId();
            }))
            : [];
    }

    /**
     * Returns the CEE journey, LD or SD, if the proof of carpooling matches it.
     *
     * @return null|LongDistanceJourney|ShortDistanceJourney
     */
    protected function _getEecJourneyFromCarpoolProof(CarpoolProof $carpoolProof)
    {
        $journey = $this->_getEecSdJourneyFromCarpoolProof($carpoolProof);

        if (
            is_null($journey)
            && !is_null($carpoolProof->getCarpoolItem())
        ) {
            $journey = $this->_getEecLdJourneyFromCarpoolProof($carpoolProof);
        }

        return $journey;
    }

    protected function _getEecSdJourneyFromCarpoolProof(CarpoolProof $carpoolProof): ?ShortDistanceJourney
    {
        return $carpoolProof->getMobConnectShortDistanceJourney();
    }

    protected function _getEecLdJourneyFromCarpoolProof(CarpoolProof $carpoolProof): ?LongDistanceJourney
    {
        return $this->_longDistanceJourneyRepository->findOneByCarpoolItemOrProposal(
            $carpoolProof->getCarpoolItem(),
            $this->getDriverPassengerProposalForCarpoolItem($carpoolProof->getCarpoolItem(), self::DRIVER)
        );
    }

    protected function hasRequestErrorReturned(MobConnectResponseInterface $response): bool
    {
        $result = in_array($response->getCode(), MobConnectResponse::ERROR_CODES);

        if (true === $result) {
            $this->_loggerService->log('The mobConnect request returned an error: '.$response->getContent(), 'error', true);
        }

        return $result;
    }

    protected function isValidParameters(): bool
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
            );
    }

    protected function getMobSubscription(string $subscriptionId)
    {
        $this->setApiProvider();

        return $this->_apiProvider->getMobSubscription($subscriptionId);
    }

    protected function getRPCOperatorId(int $id): string
    {
        return $this->_carpoolProofPrefix.$id;
    }

    protected function getDriverLongSubscriptionId(): string
    {
        return $this->getDriver()->getLongDistanceSubscription()->getSubscriptionId();
    }

    protected function getDriverShortSubscriptionId(): string
    {
        return $this->getDriver()->getShortDistanceSubscription()->getSubscriptionId();
    }

    protected function getDriverSubscriptionTimestamps(string $subscriptionId): MobConnectSubscriptionTimestampsResponse
    {
        $this->setApiProvider();

        return $this->_apiProvider->getUserSubscriptionTimestamps($subscriptionId);
    }

    protected function postSubscription(bool $isLongDistance = true): MobConnectSubscriptionResponse
    {
        $this->setApiProvider();

        return $this->_apiProvider->postSubscription($isLongDistance);
    }

    protected function patchSubscription(string $subscriptionId, array $params)
    {
        $this->setApiProvider();

        return $this->_apiProvider->patchUserSubscription($subscriptionId, $params);
    }

    protected function hasSubscriptionCommited(string $subscriptionId): bool
    {
        $this->setApiProvider();

        // TODO: Add the query allowing you to know if a subscription has been commited

        return false;
    }

    /**
     * Sets subscription expiration date.
     */
    protected function getExpirationDate(int $delay): \DateTime
    {
        $now = new \DateTime('now');

        return $now->add(new \DateInterval('P'.$delay.'M'));
    }

    protected function executeRequestVerifySubscription(string $subscriptionId)
    {
        $this->setApiProvider();

        return $this->_apiProvider->verifyUserSubscription($subscriptionId);
    }

    protected function setApiProvider()
    {
        $this->_apiProvider = new MobConnectApiProvider(
            $this->_em,
            new MobConnectApiParams($this->_mobConnectParams),
            $this->_loggerService,
            $this->_driver,
            $this->_ssoServices,
            $this->_instanceManager->getEecInstance()
        );
    }

    protected function getCarpoolersNumber(Ask $ask): int
    {
        $conn = $this->_em->getConnection();

        $sql = 'SELECT DISTINCT ci.debtor_user_id FROM carpool_item ci WHERE ci.ask_id = '.$ask->getId().'';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return count($stmt->fetchAll(\PDO::FETCH_COLUMN)) + 1;
    }

    protected function getDriver(): User
    {
        return $this->_driver;
    }

    protected function getDriverPassengerProposalForCarpoolItem(CarpoolItem $carpoolItem, int $carpoolerType): ?Proposal
    {
        $proposal = null;

        $user = self::DRIVER === $carpoolerType ? $carpoolItem->getCreditorUser() : $carpoolItem->getDebtorUser();

        switch ($user->getId()) {
            case $carpoolItem->getAsk()->getMatching()->getProposalOffer()->getUser()->getId():
                $proposal = $carpoolItem->getAsk()->getMatching()->getProposalOffer();

                break;

            case $carpoolItem->getAsk()->getMatching()->getProposalRequest()->getUser()->getId():
                $proposal = $carpoolItem->getAsk()->getMatching()->getProposalRequest();

                break;
        }

        return $proposal;
    }

    protected function getAddressesLocality(CarpoolItem $carpoolItem): array
    {
        $addresses = [
            'origin' => null,
            'destination' => null,
        ];

        $waypoints = $carpoolItem->getAsk()->getMatching()->getWaypoints();

        foreach ($carpoolItem->getAsk()->getMatching()->getWaypoints() as $waypoint) {
            if (0 === $waypoint->getPosition() && !$waypoint->isDestination()) {
                $addresses['origin'] = $waypoint->getAddress()->getAddressLocality();
            }
            if ($waypoint->isDestination()) {
                $addresses['destination'] = $waypoint->getAddress()->getAddressLocality();
            }
        }

        return $addresses;
    }

    protected function getThresholdDate(): \DateTime
    {
        $now = new \DateTime('now');
        $thresholdDate = clone $now;
        $thresholdDate->sub(new \DateInterval('P'.self::WAITING_PERIOD.'M'));

        return $thresholdDate;
    }

    /**
     * Returns EEC-compliant proofs obtained since a date.
     *
     * @param string $distanceType The distance type ('long' or 'short')
     */
    protected function getEECCompliantProofsObtainedSinceDate(string $distanceType): array
    {
        if (LongDistanceSubscription::SUBSCRIPTION_TYPE != $distanceType && ShortDistanceSubscription::SUBSCRIPTION_TYPE != $distanceType) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "The value of the '\$distanceType' parameter is incorrect");
        }

        if (is_null($this->getDriver())) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Driver must be defined before you can list proof');
        }

        $thresholdDate = $this->getThresholdDate();

        return array_values(
            array_filter($this->getDriver()->getCarpoolProofsAsDriver(), function (CarpoolProof $carpoolProof) use ($thresholdDate) {
                return
                    $carpoolProof->getCreatedDate() > $thresholdDate
                    && $carpoolProof->isEECCompliant();
            })
        );
    }

    /**
     * Returns if the driver's account meets the conditions to subscribe to EEC incentives:
     * - The driver moB Connect authentication is valid
     * - The driver has not made any trip that complies with the EEC standard since the threshold date.
     */
    protected function isDriverAccountReadyForSubscription(string $distanceType): bool
    {
        return
            $this->_userValidation->isUserValid($this->getDriver())
            && 0 === count($this->getEECCompliantProofsObtainedSinceDate($distanceType));
    }

    protected function getDistanceTraveled(CarpoolProof $carpoolProof): ?int
    {
        return
            !is_null($carpoolProof->getAsk())
            && !is_null($carpoolProof->getAsk()->getMatching())
            ? $carpoolProof->getAsk()->getMatching()->getCommonDistance() : null;
    }

    protected function isJourneyPaid(CarpoolProof $carpoolProof): bool
    {
        return !is_null($carpoolProof->getSuccessfullPayment());
    }

    protected function isLongDistance(?int $distance): bool
    {
        return !is_null($distance) && CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS <= $distance;
    }

    protected function isShortDistance(?int $distance): bool
    {
        return !is_null($distance) && CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS > $distance;
    }

    /**
     * Returns if the distance passed as an argument is long or short.
     */
    protected function getDistanceType(int $distance): ?string
    {
        return
            $this->isLongDistance($distance)
            ? LongDistanceSubscription::SUBSCRIPTION_TYPE
            : (
                $this->isShortDistance($distance)
                ? ShortDistanceSubscription::SUBSCRIPTION_TYPE
                : null
            );
    }

    protected function getIncentives(): ?IncentivesResponse
    {
        $this->setApiProvider();

        return $this->_apiProvider->getIncentives();
    }

    protected function getIncentive(string $incentive_id): ?IncentiveResponse
    {
        $this->setApiProvider();

        return $this->_apiProvider->getIncentive($incentive_id);
    }

    protected function getCommitmentRequestParams(): array
    {
        return $this->_currentSubscription instanceof LongDistanceSubscription
            ? [
                SpecificFields::JOURNEY_ID => LongDistanceSubscription::COMMITMENT_PREFIX.$this->_currentProposal->getId(),
                SpecificFields::JOURNEY_PUBLISH_DATE => $this->_currentProposal->getCreatedDate()->format(self::DATE_FORMAT),
            ]
            : [
                SpecificFields::JOURNEY_ID => $this->getRPCOperatorId($this->_currentCarpoolProof->getId()),
                SpecificFields::JOURNEY_START_DATE => $this->_currentCarpoolProof->getPickUpDriverDate()->format(self::DATE_FORMAT),
            ];
    }
}
