<?php

namespace App\Incentive\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class EecVoter extends Voter
{
    public const USER_ADMIN_EEC = 'admin_eec';

    protected $authManager;
    protected $user;

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::USER_ADMIN_EEC,
        ])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::USER_ADMIN_EEC: return $this->canAdministerCEE();
        }

        throw new \LogicException('This code should not be reached!');
    }

    protected function canAdministerCEE(): bool
    {
        return $this->authManager->isAuthorized(self::USER_ADMIN_EEC);
    }
}
