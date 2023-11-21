<?php

namespace App\Incentive\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Security;

class ShortDistanceSubscriptionVoter extends EecVoter
{
    public function __construct(AuthManager $authManager, Security $security)
    {
        $this->authManager = $authManager;
        $this->user = $security->getUser();
    }
}
