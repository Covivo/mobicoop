<?php

namespace App\Incentive\Service\Manager;

use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Token;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\LoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TimestampTokenManager extends MobConnectManager
{
    /**
     * @var EecInstance
     */
    protected $_eecInstance;

    /**
     * @var MobConnectSubscriptionTimestampsResponse
     */
    private $_currentTimestampTokensResponse;

    public function __construct(
        EntityManagerInterface $em,
        LoggerService $loggerService,
        InstanceManager $instanceManager
    ) {
        $this->_eecInstance = $instanceManager->getEecInstance();

        parent::__construct($em, $instanceManager, $loggerService);
    }

    public function getMobTimestampToken($subscription): MobConnectSubscriptionTimestampsResponse
    {
        $this->_setCurrentSubscription($subscription);

        return $this->_getTimestamps();
    }

    /**
     * Updates one of a subscription tokens.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function getLatestToken($subscription): ?Token
    {
        if (is_null($subscription)) {
            return null;
        }

        $this->_setCurrentSubscription($subscription);

        $this->_setCurrentTimestampTokensResponse();

        if (!is_null($this->_currentTimestampTokensResponse->getTokens() && !empty($this->_currentTimestampTokensResponse->getTokens()))) {
            $tokens = $this->_currentTimestampTokensResponse->getTokens();
            $latestToken = end($tokens);

            return new Token($latestToken->timestampToken, $latestToken->signingTime);
        }

        return null;
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    private function _setCurrentSubscription($subscription): self
    {
        $this->_currentSubscription = $subscription;

        if (!is_null($this->_currentSubscription)) {
            $this->setDriver($this->_currentSubscription->getUser());
        }

        return $this;
    }

    private function _setCurrentTimestampTokensResponse(): self
    {
        $this->_currentTimestampTokensResponse = $this->_getTimestamps();

        return $this;
    }

    /**
     * @throws HttpException
     */
    private function _getTimestamps(): MobConnectSubscriptionTimestampsResponse
    {
        $provider = new MobConnectApiProvider($this->_eecInstance);

        return $provider->getSubscriptionTimestamps($this->_currentSubscription->getSubscriptionId());
    }
}
