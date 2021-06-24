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

namespace App\User\Interoperability\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\User\Interoperability\Ressource\User;
use App\User\Entity\User as UserEntity;
use App\User\Service\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const USER_CREATE = 'interop_user_create';
    const USER_READ = 'interop_user_read';
    const USER_UPDATE = 'interop_user_update';
    
    private $authManager;
    private $userManager;

    public function __construct(AuthManager $authManager, UserManager $userManager)
    {
        $this->authManager = $authManager;
        $this->userManager = $userManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::USER_CREATE,
            self::USER_READ,
            self::USER_UPDATE,
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::USER_CREATE,
            self::USER_READ,
            self::USER_UPDATE,
            ]) && !($subject instanceof Paginator) && !$subject instanceof User) {
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
                return $this->canReadUser($this->userManager->getUser($subject->getId()));
            case self::USER_UPDATE:
                return $this->canUpdateUser($this->userManager->getUser($subject->getId()));
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateUser()
    {
        return $this->authManager->isAuthorized(self::USER_CREATE);
    }

    private function canReadUser(UserEntity $user)
    {
        return $this->authManager->isAuthorized(self::USER_READ, ['user'=>$user]);
    }

    private function canUpdateUser(UserEntity $user)
    {
        return $this->authManager->isAuthorized(self::USER_UPDATE, ['user'=>$user]);
    }
}
