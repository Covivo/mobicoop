<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

abstract class MobConnectManager
{
    public const BONUS_STATUS_PENDING = 0;
    public const BONUS_STATUS_NO = 1;
    public const BONUS_STATUS_OK = 2;

    public const LONG_DISTANCE_TRIP_THRESHOLD = 3;
    public const SHORT_DISTANCE_TRIP_THRESHOLD = 10;

    public const SUBSCRIPTION_EXPIRATION_DELAY = 3;     // Expressed in months

    public const LONG_SUBSCRIPTION_TYPE = 'long';
    public const SHORT_SUBSCRIPTION_TYPE = 'short';

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
     * @var LoggerService
     */
    protected $_loggerService;

    /**
     * @var HonourCertificateService
     */
    protected $_honourCertificateService;

    /**
     * @var MobConnectApiProvider
     */
    private $_apiProvider;

    /**
     * @var int
     */
    private $_carpoolProofPrefix;

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
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        $this->_em = $em;
        $this->_loggerService = $loggerService;
        $this->_honourCertificateService = $honourCertificateService;

        $this->_carpoolProofPrefix = $carpoolProofPrefix;
        $this->_mobConnectParams = $mobConnectParams;
        $this->_ssoServices = $ssoServices;
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
                && (
                    array_key_exists('subscription_ids', $this->_mobConnectParams)
                    && is_array($this->_mobConnectParams['subscription_ids'])
                    && !empty($this->_mobConnectParams['subscription_ids'])
                    && array_key_exists('short_distance', $this->_mobConnectParams['subscription_ids'])
                    && !empty($this->_mobConnectParams['subscription_ids']['short_distance'])
                    && array_key_exists('long_distance', $this->_mobConnectParams['subscription_ids'])
                    && !empty($this->_mobConnectParams['subscription_ids']['long_distance'])
                )
            );
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

    /**
     * Sets subscription expiration date.
     */
    protected function getExpirationDate(): \DateTime
    {
        $now = new \DateTime('now');

        return $now->add(new \DateInterval('P'.self::SUBSCRIPTION_EXPIRATION_DELAY.'M'));
    }

    protected function executeRequestVerifySubscription(string $subscriptionId)
    {
        $this->setApiProvider();

        return $this->_apiProvider->verifyUserSubscription($subscriptionId);
    }

    protected function setApiProvider()
    {
        $this->_apiProvider = new MobConnectApiProvider($this->_em, new MobConnectApiParams($this->_mobConnectParams), $this->_loggerService, $this->_driver, $this->_ssoServices);
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

    protected function setDriver(User $driver): self
    {
        $this->_driver = $driver;

        if (!is_null($this->_driver)) {
            $this->_honourCertificateService->setDriver($this->getDriver());
        }

        return $this;
    }

    protected function getShortDistanceCommitmentJourney(CarpoolProof $carpoolProof, ShortDistanceSubscription $subscription): ?ShortDistanceJourney
    {
        /**
         * @var ShortDistanceJourney
         */
        $commitmentJourney = $this->_em->getRepository(ShortDistanceJourney::class)->findOneBy([
            'subscription' => $subscription,
            'commitmentJourney' => true,
        ]);

        return
            !is_null($commitmentJourney)
            && !is_null($commitmentJourney->getCarpoolProof())
            && $commitmentJourney->getCarpoolProof() === $carpoolProof
            ? $commitmentJourney : null;
    }

    protected function getLongDistanceCommitmentJourney(CarpoolProof $carpoolProof, LongDistanceSubscription $subscription): ?LongDistanceJourney
    {
        switch ($carpoolProof->getDriver()) {
            case $carpoolProof->getAsk()->getMatching()->getProposalOffer()->getUser():
                $initialProposal = $carpoolProof->getAsk()->getMatching()->getProposalOffer();

                break;

            case $carpoolProof->getAsk()->getMatching()->getProposalRequest()->getUser():
                $initialProposal = $carpoolProof->getAsk()->getMatching()->getProposalRequest();

                break;

            default:
                $initialProposal = null;
        }

        /**
         * @var LongDistanceJourney
         */
        $commitmentJourney = $this->_em->getRepository(LongDistanceJourney::class)->findOneBy([
            'subscription' => $subscription,
            'commitmentJourney' => true,
        ]);

        return
            !is_null($initialProposal)
            && !is_null($commitmentJourney)
            && !is_null($commitmentJourney->getInitialProposal())
            && $commitmentJourney->getInitialProposal() === $initialProposal
            ? $commitmentJourney : null;
    }
}
