<?php

namespace App\Incentive\Validator;

use App\User\Entity\User;

class UserValidator
{
    public static function hasUserEECSubscribed(User $user): bool
    {
        return !is_null($user->getShortDistanceSubscription()) || !is_null($user->getLongDistanceSubscription());
    }
}
