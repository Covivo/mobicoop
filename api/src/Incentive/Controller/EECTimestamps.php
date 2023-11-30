<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EECTimestamps
{
    /**
     * @var Request
     */
    private $_request;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(RequestStack $request, SubscriptionManager $subscriptionManager)
    {
        $this->_request = $request->getCurrentRequest();
        $this->_subscriptionManager = $subscriptionManager;
    }

    public function __invoke(User $user): ?User
    {
        if (boolval($this->_request->get('update'))) {
            $user = $this->_subscriptionManager->updateTimestampTokens($user);
        }

        return $user;
    }
}
