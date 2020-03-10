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

use App\Auth\Service\AuthManager;
use App\Auth\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    const REGISTER = 'user_register';
    const READ = 'user_read';
    const UPDATE = 'user_update';
    const PASSWORD = 'user_password';
    const DELETE = 'user_delete';
    const ASKS = 'user_asks';
    const MESSAGES = 'user_messages';
    const ADMIN_READ = 'user_admin_read';

    private $security;
    private $permissionManager;
    private $authManager;

    public function __construct(Security $security, PermissionManager $permissionManager, AuthManager $authManager)
    {
        $this->security = $security;
        $this->permissionManager = $permissionManager;
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::REGISTER,
            self::READ,
            self::UPDATE,
            self::PASSWORD,
            self::DELETE,
            self::ASKS,
            self::MESSAGES,
            self::ADMIN_READ
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
            case self::REGISTER:
                return $this->canRegister($requester);
            case self::READ:
                return $this->canRead($requester, $subject);
            case self::UPDATE:
                return $this->canUpdate($requester, $subject);
            case self::PASSWORD:
                return $this->canChangePassword($requester);
            case self::DELETE:
                return $this->canDeleteSelf($requester, $subject);
            case self::ASKS:
                return $this->canReadSelfAsks($requester, $subject);
            case self::MESSAGES:
                return $this->canReadSelfMessages($requester, $subject);
            case self::ADMIN_READ:
                return $this->canReadUsers($requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canRegister(UserInterface $requester)
    {
        if ($requester instanceof User) {
            // the user must not be logged in; if not, deny access
            return false;
        }
        return $this->permissionManager->checkPermission('user_register', $requester);
    }

    private function canRead(UserInterface $requester, User $subject)
    {
        //return $this->permissionManager->checkPermission('user_read', $requester, null, $subject->getId());
        // var_dump($this->authManager->getTerritoriesForItem('user_read'));
        // exit;
        return $this->authManager->isAuthorized('user_read', ['id'=>$subject->getId()]);
    }

    private function canUpdate(UserInterface $requester, User $subject)
    {
        return $this->permissionManager->checkPermission('user_update', $requester, null, $subject->getId());
    }

    private function canChangePassword(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('user_password', $requester);
    }

    private function canDeleteSelf(UserInterface $requester, User $subject)
    {
        if (($subject->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_delete_self', $requester);
        } else {
            return false;
        }
    }

    private function canReadSelfAsks(UserInterface $requester, User $subject)
    {
        if (($subject->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_asks_self', $requester);
        } else {
            return false;
        }
    }

    private function canReadSelfMessages(UserInterface $requester, User $subject)
    {
        return $this->permissionManager->checkPermission('user_message_read_self', $requester, null, $subject->getId());
    }

    private function canReadUsers(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('user_read', $requester);
    }
}
