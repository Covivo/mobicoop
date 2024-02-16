<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Entity\MobConnect\Response\IncentiveResponse;
use App\DataProvider\Entity\MobConnect\Response\IncentivesResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\UserValidation;
use App\Incentive\Validator\CarpoolProofValidator;
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

    public function __construct(
        EntityManagerInterface $em,
        InstanceManager $instanceManager,
        LoggerService $loggerService
    ) {
        $this->_em = $em;

        $this->_instanceManager = $instanceManager;

        $this->_loggerService = $loggerService;
    }

    public function setDriver(User $driver): self
    {
        $this->_driver = $driver;

        return $this;
    }

    protected function setApiProvider()
    {
        $this->_apiProvider = new MobConnectApiProvider($this->_instanceManager->getEecInstance());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     *
     * @return bool|MobConnectSubscriptionResponse
     */
    protected function getSubscription($subscription, User $user)
    {
        $this->setApiProvider();

        return $this->_apiProvider->getSubscription($subscription, $user);
    }

    protected function getIncentives(User $user): ?IncentivesResponse
    {
        $this->setApiProvider();

        return $this->_apiProvider->getIncentives($user);
    }

    protected function getIncentive(string $incentiveId, User $user): ?IncentiveResponse
    {
        $this->setApiProvider();

        return $this->_apiProvider->getIncentive($incentiveId, $user);
    }

    protected function getDriver(): User
    {
        return $this->_driver;
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
                    && CarpoolProofValidator::isEecCompliant($carpoolProof);
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
}
