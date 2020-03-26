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
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\User\Entity\User;
use App\Solidary\Entity\SolidaryUser;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const USER_CREATE = 'user_create';
    const USER_READ = 'user_read';
    const USER_UPDATE = 'user_update';
    const USER_DELETE = 'user_delete';
    const USER_LIST = 'user_list';
    const USER_PASSWORD = 'user_password';
    const USER_REGISTER = 'user_register';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::USER_CREATE,
            self::USER_READ,
            self::USER_UPDATE,
            self::USER_DELETE,
            self::USER_LIST,
            self::USER_PASSWORD,
            self::USER_REGISTER
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::USER_CREATE,
            self::USER_READ,
            self::USER_UPDATE,
            self::USER_DELETE,
            self::USER_LIST,
            self::USER_PASSWORD,
            self::USER_REGISTER
            ]) && !($subject instanceof Paginator) && !($subject instanceof User || $subject instanceof SolidaryUser)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::USER_CREATE:
                return $this->canCreateUser();
            case self::USER_READ:
                ($subject instanceof SolidaryUser) ? $user = $subject->getUser() : $user = $subject;
                return $this->canReadUser($user);
            case self::USER_UPDATE:
                ($subject instanceof SolidaryUser) ? $user = $subject->getUser() : $user = $subject;
                return $this->canUpdateUser($user);
            case self::USER_DELETE:
                ($subject instanceof SolidaryUser) ? $user = $subject->getUser() : $user = $subject;
                return $this->canDeleteUser($user);
            case self::USER_LIST:
                return $this->canListUser();
            case self::USER_PASSWORD:
                return $this->canChangePassword($subject);
            case self::USER_REGISTER:
                return $this->canRegister();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateUser()
    {
        return $this->authManager->isAuthorized(self::USER_CREATE);
    }

    private function canReadUser(User $user)
    {
        return $this->authManager->isAuthorized(self::USER_READ, ['user'=>$user]);
    }

    private function canUpdateUser(User $user)
    {
        return $this->authManager->isAuthorized(self::USER_UPDATE, ['user'=>$user]);
    }
    
    private function canDeleteUser(User $user)
    {
        return $this->authManager->isAuthorized(self::USER_DELETE, ['user'=>$user]);
    }
    
    private function canListUser()
    {
        return $this->authManager->isAuthorized(self::USER_LIST);
    }

    private function canChangePassword(User $user)
    {
        return $this->authManager->isAuthorized(self::USER_LIST, ['user'=>$user]);
    }

    private function canRegister()
    {
        return $this->authManager->isAuthorized(self::USER_REGISTER);
    }
}
