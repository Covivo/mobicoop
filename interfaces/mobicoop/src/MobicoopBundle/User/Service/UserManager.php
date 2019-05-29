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

namespace Mobicoop\Bundle\MobicoopBundle\User\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;
use DateTime;

/**
 * User management service.
 */
class UserManager
{
    private $dataProvider;
    private $encoder;
    private $tokenStorage;
    private $logger;

    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     * @param UserPasswordEncoderInterface $encoder
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(DataProvider $dataProvider, UserPasswordEncoderInterface $encoder, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(User::class);
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }
    
    /**
     * Get a user by its identifier
     *
     * @param String $id The user id
     *
     * @return User|null The user found or null if not found.
     */
    public function getUser($id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $user = $response->getValue();
            if ($user->getBirthDate()) {
                $user->setBirthYear($user->getBirthDate()->format('Y'));
            }
            $this->logger->info('User | Is found');
            return $user;
        }
        $this->logger->error('User | is Not found');
        return null;
    }
    
    /**
     * Get the logged user.
     *
     * @return User|null The logged user found or null if not found.
     */
    public function getLoggedUser()
    {
        if ($this->tokenStorage->getToken() === null) {
            return null;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            if ($user->getBirthDate()) {
                $user->setBirthYear($user->getBirthDate()->format('Y'));
            }
            $this->logger->info('User | Is logged');
            return $user;
        }
        $this->logger->error('User | Not logged');
        return null;
    }
    
    /**
     * Get all users
     *
     * @return array|null The users found or null if not found.
     */
    public function getUsers()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            $this->logger->info('User | Found');
            return $response->getValue();
        }
        $this->logger->error('User | Not found');
        return null;
    }
    
    /**
     * Create a user
     *
     * @param User $user The user to create
     *
     * @return User|null The user created or null if error.
     */
    public function createUser(User $user)
    {
        // encoding of the password
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
        // set the birthdate
        $birthdate = DateTime::createFromFormat('Y-m-d', $user->getBirthYear() . '-1-1');
        $user->setBirthDate($birthdate);
        $response = $this->dataProvider->post($user);
        if ($response->getCode() == 201) {
            $this->logger->info('User Creation | Start');
            return $response->getValue();
        }
        $this->logger->error('User Creation | Fail');
        return null;
    }
    
    /**
     * Update a user
     *
     * @param User $user The user to update
     *
     * @return User|null The user updated or null if error.
     */
    public function updateUser(User $user)
    {
        $response = $this->dataProvider->put($user);
        if ($response->getCode() == 200) {
            $this->logger->info('User Update | Start');
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Update a user password
     *
     * @param User $user The user to update the password
     *
     * @return User|null The user updated or null if error.
     */
    public function updateUserPassword(User $user)
    {
        // encoding of the password
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
        $response = $this->dataProvider->put($user);
        if ($response->getCode() == 200) {
            $this->logger->info('User Password Update | Start');
            return $response->getValue();
        }
        $this->logger->info('User Password Update | Fail');
        return null;
    }
    
    /**
     * Delete a user
     *
     * @param int $id The id of the user to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteUser(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
            $this->logger->info('User Deleta | Start');
        }
        $this->logger->info('User Delete | FaiL');
        return false;
    }
}
