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
use App\User\Exception\BadRequestInteroperabilityUserException;
use App\User\Interoperability\Ressource\DetachSso;
use App\User\Service\UserManager as UserEntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Interoperability User manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserManager
{
    private $userEntityManager;
    private $security;
    private $entityManager;

    public function __construct(UserEntityManager $userEntityManager, Security $security, EntityManagerInterface $entityManager)
    {
        $this->userEntityManager = $userEntityManager;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    
    /**
     * Get a User
     *
     * @param integer $id   User's Id
     * @return User The interoperabily User
     */
    public function getUser(int $id): ?User
    {
        $userEntity = $this->userEntityManager->getUser($id);
        $user = null;
        if ($userEntity) {
            $user = $this->buildUserFromUserEntity($userEntity);
        }
        return $user;
    }
    
    /**
     * Register a User
     *
     * @param User $user    The interoperabily User to register
     * @return User The interoperabily User registered
     */
    public function registerUser(User $user): User
    {
        if (!is_null($this->userEntityManager->getUserByEmail($user->getEmail()))) {
            throw new BadRequestInteroperabilityUserException(BadRequestInteroperabilityUserException::USER_ALREADY_EXISTS);
        }
        $userEntity = $this->buildUserEntityFromUser($user);
        $userEntity = $this->userEntityManager->registerUser($userEntity);

        return $this->buildUserFromUserEntity($userEntity);
    }

    /**
     * Update the entity User associated to an Interoperability User
     *
     * @param User $user The interoperability User
     * @return User The interoperability User
     */
    public function updateUser(User $user): User
    {
        if ($userEntity = $this->userEntityManager->getUser($user->getId())) {
            $userEntity->setGivenName($user->getGivenName());
            $userEntity->setFamilyName($user->getFamilyName());
            $userEntity->setGender($user->getGender());
            $userEntity->setEmail($user->getEmail());
            $userEntity->setNewsSubscription($user->hasNewsSubscription());

            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
        }
        return $user;
    }

    /**
     * Erase the SsoId and the SsoProvider informations of the user account
     *
     * @param DetachSso $detachSso The data about the sso account to detach
     * @return DetachSso
     */
    public function detachUser(DetachSso $detachSso): DetachSso
    {
        if ($userEntity = $this->userEntityManager->getUserBySsoId($detachSso->getUuid())) {
            $userEntity->setSsoId(null);
            $userEntity->setSsoProvider(null);

            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();

            $detachSso->setUserId($userEntity->getId());
        }
        return $detachSso;
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
        $user->setGivenName($userEntity->getGivenName());
        $user->setFamilyName($userEntity->getFamilyName());
        $user->setGender($userEntity->getGender());
        $user->setEmail($userEntity->getEmail());
        $user->setNewsSubscription($userEntity->hasNewsSubscription());

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
        $userEntity->setNewsSubscription($user->hasNewsSubscription());
        $userEntity->setAppDelegate($this->security->getUser());
        return $userEntity;
    }
}
