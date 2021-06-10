<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\User\Admin\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\User\Entity\User;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const ADMIN_USER_CREATE = 'admin_user_create';
    const ADMIN_USER_READ = 'admin_user_read';
    const ADMIN_USER_UPDATE = 'admin_user_update';
    const ADMIN_USER_DELETE = 'admin_user_delete';
    const ADMIN_USER_LIST = 'admin_user_list';
    const USER_CREATE = 'user_create';
    const USER_READ = 'user_read';
    const USER_UPDATE = 'user_update';
    const USER_DELETE = 'user_delete';
    const USER_LIST = 'user_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_USER_CREATE,
            self::ADMIN_USER_READ,
            self::ADMIN_USER_UPDATE,
            self::ADMIN_USER_DELETE,
            self::ADMIN_USER_LIST
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_USER_CREATE,
            self::ADMIN_USER_READ,
            self::ADMIN_USER_UPDATE,
            self::ADMIN_USER_DELETE,
            self::ADMIN_USER_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof User)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_USER_CREATE:
                return $this->canCreateUser();
            case self::ADMIN_USER_READ:
                return $this->canReadUser($subject);
            case self::ADMIN_USER_UPDATE:
                return $this->canUpdateUser($subject);
            case self::ADMIN_USER_DELETE:
                return $this->canDeleteUser($subject);
            case self::ADMIN_USER_LIST:
                return $this->canListUser();
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
}
