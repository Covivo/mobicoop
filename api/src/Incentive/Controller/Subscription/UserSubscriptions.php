<?php

namespace App\Incentive\Controller\Subscription;

use App\User\Entity\User;

class UserSubscriptions
{
    public function __invoke(User $user): User
    {
        return $user;
    }
}
