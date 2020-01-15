<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\User\Security;

use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    const READ = 'user_read';
    const UPDATE = 'update';
    const PASSWORD = 'password';
    const DELETE = 'delete';
    const PROPOSALS_SELF = 'proposals_self';
    const MESSAGES = 'messages';

    private $security;
    private $permissionManager;

    public function __construct(Security $security, PermissionManager $permissionManager)
    {
        $this->security = $security;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::READ,
            self::UPDATE,
            self::PASSWORD,
            self::DELETE,
            self::PROPOSALS_SELF,
            self::MESSAGES,
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
        $requester = $token->getUser();
        
        switch ($attribute) {
            case self::READ:
                return $this->canReadSelf($requester);
            case self::UPDATE:
                return $this->canUpdateSelf($requester);
            case self::PASSWORD:
                return $this->canChangePassword($requester);
            case self::DELETE:
                return $this->canDeleteSelf($requester);
            case self::PROPOSALS_SELF:
                return true;
            case self::MESSAGES:
                return true;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadSelf(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('user_read_self', $requester);
    }

    private function canUpdateSelf(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('user_update_self', $requester);
    }

    private function canChangePassword(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('user_password_self', $requester);
    }

    private function canDeleteSelf(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('user_delete_self', $requester);
    }
}
