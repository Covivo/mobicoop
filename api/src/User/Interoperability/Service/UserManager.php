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

namespace App\User\Interoperability\Service;

use App\User\Interoperability\Ressource\User;
use App\User\Entity\User as UserEntity;
use App\User\Service\UserManager as UserEntityManager;

/**
 * Interoperability User manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserManager
{
    private $userEntityManager;

    public function __construct(UserEntityManager $userEntityManager)
    {
        $this->userEntityManager = $userEntityManager;
    }

    /**
     * Register a User
     *
     * @param User $user    The interoperabily User to register
     * @return User The interoperabily User registered
     */
    public function registerUser(User $user): User
    {
        $userEntity = $this->buildUserEntityFromUser($user);
        $userEntity = $this->userEntityManager->registerUser($userEntity);

        return $this->buildUserFromUserEntity($userEntity);
    }

    /**
     * Build an interoperability User from a classic User entity
     *
     * @param UserEntity $userEntity    The classic User Entity
     * @return User The interoperability User
     */
    private function buildUserFromUserEntity(UserEntity $userEntity): User
    {
        $user = new User($userEntity->getId());
        $userEntity->setGivenName($user->getGivenName());
        $userEntity->setFamilyName($user->getFamilyName());
        $userEntity->setGender($user->getGender());
        $userEntity->setEmail($user->getEmail());

        return $user;
    }

    /**
     * Build a classic User Entity from an interoperability User
     *
     * @param User $user    The interoperability User
     * @return UserEntity   The classic User Entity
     */
    private function buildUserEntityFromUser(User $user): UserEntity
    {
        $userEntity = new UserEntity();
        $userEntity->setId($user->getId());
        $userEntity->setGivenName($user->getGivenName());
        $userEntity->setFamilyName($user->getFamilyName());
        $userEntity->setGender($user->getGender());
        $userEntity->setEmail($user->getEmail());
        $userEntity->setPassword($user->getPassword());

        return $userEntity;
    }
}
