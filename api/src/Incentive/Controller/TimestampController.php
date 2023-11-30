<?php

namespace App\Incentive\Controller;

use App\Auth\Service\AuthManager;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\TimestampTokenManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/timestamps")
 */
class TimestampController
{
    /**
     * @var AuthManager
     */
    private $_authManager;

    /**
     * @var TimestampTokenManager
     */
    private $_timestampTokenManager;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_currentSubscription;

    public function __construct(AuthManager $authManager, TimestampTokenManager $timestampTokenManager)
    {
        $this->_authManager = $authManager;
        $this->_timestampTokenManager = $timestampTokenManager;
    }

    /**
     * @Route("/LD/{subscription}")
     */
    public function getLdSubscriptionTimestampsTokens(LongDistanceSubscription $subscription)
    {
        $this->_currentSubscription = $subscription;

        return $this->getSubscriptionTimestampTokens();
    }

    /**
     * @Route("/SD/{subscription}")
     */
    public function geSdSubscriptionTimestampsTokens(ShortDistanceSubscription $subscription)
    {
        $this->_currentSubscription = $subscription;

        return $this->getSubscriptionTimestampTokens();
    }

    private function getSubscriptionTimestampTokens()
    {
        if (!$this->_authManager->isAuthorized('admin_eec')) {
            throw new AccessDeniedHttpException();
        }

        $response = $this->_timestampTokenManager->getMobTimestampToken($this->_currentSubscription);

        return new JsonResponse($response->getContent());
    }
}
