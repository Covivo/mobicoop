<?php

namespace App\User\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use App\Incentive\Service\MobConnectMessages;
use App\TranslatorTrait;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EECSubscription
{
    use TranslatorTrait;

    /**
     * @var bool
     */
    private $_isSubscriptionServiceActive = false;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    /**
     * @var Request
     */
    private $_request;

    public function __construct(RequestStack $requestStack, SubscriptionManager $subscriptionManager, string $ceeSubscriptionProvider)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_subscriptionManager = $subscriptionManager;
        $this->_isSubscriptionServiceActive = !empty($ceeSubscriptionProvider);
    }

    public function __invoke(User $user): ?User
    {
        if (!$this->_isSubscriptionServiceActive) {
            return $user;
        }

        if (is_null($user->getMobConnectAuth())) {
            throw new BadRequestHttpException(MobConnectMessages::MOB_CONNECTION_ERROR);
        }

        $this->_subscriptionManager->createSubscriptions($user);

        return $user;
    }
}
