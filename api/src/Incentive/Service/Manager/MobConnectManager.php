<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\Ask;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Service\Checker\JourneyChecker;
use App\Incentive\Service\LoggerService;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

abstract class MobConnectManager
{
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
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        $this->_em = $em;
        $this->_journeyChecker = $journeyChecker;
        $this->_loggerService = $loggerService;

        $this->_carpoolProofPrefix = $carpoolProofPrefix;
        $this->_mobConnectParams = $mobConnectParams;
        $this->_ssoServices = $ssoServices;
    }

    protected function getRPCOperatorId(int $id): string
    {
        return $this->_carpoolProofPrefix.$id;
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

    protected function getDriver(): User
    {
        return $this->_driver;
    }

    protected function setDriver(User $driver): self
    {
        $this->_driver = $driver;

        return $this;
    }
}
