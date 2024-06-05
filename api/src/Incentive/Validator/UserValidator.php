<?php

namespace App\Incentive\Validator;

use App\User\Entity\User;

class UserValidator
{
    public static function hasUserEECSubscribed(User $user): bool
    {
        return !is_null($user->getShortDistanceSubscription()) || !is_null($user->getLongDistanceSubscription());
    }

    public static function isUserAddressFullyCompleted(User $user): bool
    {
        $homeAddress = $user->getHomeAddress();

        return
            !is_null($homeAddress)
            && (!is_null($homeAddress->getStreetAddress()) && !empty($homeAddress->getStreetAddress()))
            && (!is_null($homeAddress->getPostalCode()) && !empty($homeAddress->getPostalCode()))
            && (!is_null($homeAddress->getAddressLocality()) && !empty($homeAddress->getAddressLocality()));
    }
}
