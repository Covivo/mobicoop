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
use App\User\Interoperability\Ressource\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const INTEROP_USER_CREATE = 'interop_user_create';
    const INTEROP_USER_READ = 'interop_user_read';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::INTEROP_USER_CREATE,
            self::INTEROP_USER_READ
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::INTEROP_USER_CREATE,
            self::INTEROP_USER_READ
            ]) && !($subject instanceof Paginator) && !$subject instanceof User) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::INTEROP_USER_CREATE:
                return $this->canCreateUser();
            case self::INTEROP_USER_READ:
                return $this->canReadUser($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateUser()
    {
        return $this->authManager->isAuthorized(self::INTEROP_USER_CREATE);
    }

    private function canReadUser(User $user)
    {
        return $this->authManager->isAuthorized(self::INTEROP_USER_READ, ['user'=>$user]);
    }
}
