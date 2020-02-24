<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\User\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Permission\Service\PermissionManager;

class RegisteredUserVoter extends Voter
{
    const PROFILE = 'profile';
    const UPDATE = 'update';
    const PASSWORD = 'password';
    const DELETE = 'delete';
    const PROPOSALS_SELF = 'proposals_self';
    const MESSAGES = 'messages';
    const ADDRESS_UPDATE_SELF = 'address_update_self';

    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::PROFILE,
            self::UPDATE,
            self::PASSWORD,
            self::DELETE,
            self::PROPOSALS_SELF,
            self::MESSAGES,
            self::ADDRESS_UPDATE_SELF
            ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $subject;

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::PROFILE:
                return true;
            case self::UPDATE:
                return $this->canUpdateSelf($user);
            case self::PASSWORD:
                return $this->canChangePassword($user);
            case self::DELETE:
                return $this->canDeleteSelf($user);
            case self::PROPOSALS_SELF:
                return true;
            case self::MESSAGES:
                return true;
            case self::ADDRESS_UPDATE_SELF:
                return $this->canAddressUpdateSelf($user);
            
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canUpdateSelf(User $user)
    {
        return $this->permissionManager->checkPermission('user_update', $user, $user->getId());
    }

    private function canChangePassword(User $user)
    {
        return $this->permissionManager->checkPermission('user_password', $user, $user->getId());
    }

    private function canDeleteSelf(User $user)
    {
        return $this->permissionManager->checkPermission('user_delete', $user, $user->getId());
    }
    
    private function canAddressUpdateSelf(User $user)
    {
        // To DO : user_address_update Action user_address_update not found
        return $this->permissionManager->checkPermission('user_address_update', $user, $user->getId());
    }
}
