<?php

namespace App\Incentive\Service\Validation;

use App\User\Entity\User;

abstract class APIAuthenticationValidation
{
    public static function isAuthenticationValid(User $user): bool
    {
        return $user->getMobConnectAuth() && $user->getMobConnectAuth()->getValidity();
    }
}
