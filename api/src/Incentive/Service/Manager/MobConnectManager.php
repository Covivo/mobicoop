<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Service\Checker\JourneyChecker;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

abstract class MobConnectManager
{
    public const BONUS_STATUS_PENDING = 0;
    public const BONUS_STATUS_NO = 1;
    public const BONUS_STATUS_OK = 2;

    public const LONG_DISTANCE_TRIP_THRESHOLD = 3;
    public const SHORT_DISTANCE_TRIP_THRESHOLD = 10;

    /**
     * @var User
     */
    protected $_driver;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var JourneyChecker
     */
    protected $_journeyChecker;

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
        JourneyChecker $journeyChecker,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        $this->_em = $em;
        $this->_journeyChecker = $journeyChecker;
        $this->_loggerService = $loggerService;
        $this->_honourCertificateService = $honourCertificateService;

        $this->_carpoolProofPrefix = $carpoolProofPrefix;
        $this->_mobConnectParams = $mobConnectParams;
        $this->_ssoServices = $ssoServices;
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

    protected function patchSubscription(string $subscriptionId, array $params)
    {
        $this->setApiProvider();

        return $this->_apiProvider->patchUserSubscription($subscriptionId, $params);
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

    protected function getCarpoolProofsFromCarpoolPayment(CarpoolPayment $carpoolPayment): array
    {
        /**
         * @var CarpoolItem[]
         */
        $filteredCarpoolItems = array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth())
            ;
        });

        $carpoolProofs = [];

        foreach ($filteredCarpoolItems as $carpoolItem) {
            /**
             * @var User
             */
            $driver = $carpoolItem->getCreditorUser();

            // Checks :
            //    - The driver has purchased a long-distance journey incentive
            //    - The journey is a long distance journey
            //    - The journey origin and/or destination is the référence country
            if (
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth())
                && !is_null($driver->getLongDistanceSubscription())
                && !is_null($carpoolItem->getAsk())
                && !is_null($carpoolItem->getAsk()->getMatching())
                && $this->_journeyChecker->isDistanceLongDistance($carpoolItem->getAsk()->getMatching()->getCommonDistance())
                && !empty($carpoolItem->getAsk()->getMatching()->getWaypoints())
                && $this->_journeyChecker->isOriginOrDestinationFromFrance($carpoolItem->getAsk()->getMatching())
            ) {
                $filteredCarpoolProofs = array_filter($carpoolItem->getAsk()->getCarpoolProofs(), function (CarpoolProof $carpoolProof) use ($driver) {
                    return $carpoolProof->getDriver() === $driver;
                });

                $carpoolProofs = array_merge($carpoolProofs, $filteredCarpoolProofs);
            }

            return $carpoolProofs;
        }
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
}
