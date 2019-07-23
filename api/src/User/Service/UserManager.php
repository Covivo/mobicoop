<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Event\UserPasswordChangeAskedEvent;
use App\User\Event\UserPasswordChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Right\Repository\RoleRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;
use App\Community\Repository\CommunityRepository;
use App\Community\Entity\CommunityUser;
use App\User\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Communication\Repository\MessageRepository;

/**
 * User manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserManager
{
    private $entityManager;
    private $roleRepository;
    private $communityRepository;
    private $messageRepository;
    private $logger;
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $dispatcher, RoleRepository $roleRepository, CommunityRepository $communityRepository, MessageRepository $messageRepository)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
        $this->communityRepository = $communityRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher = $dispatcher;
    }
    
    /**
     * Registers a user.
     *
     * @param User $user    The user to register
     * @return User         The user created
     */
    public function registerUser(User $user)
    {
        // default role : user registered full
        $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL);
        $userRole = new UserRole();
        $userRole->setRole($role);
        $user->addUserRole($userRole);
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // dispatch en event
        $event = new UserRegisteredEvent($user);
        $this->eventDispatcher->dispatch(UserRegisteredEvent::NAME, $event);
        // return the user
        return $user;
    }
 
		/**
		 * Update a user.
		 *
		 * @param User $user    The user to register
		 * @return User         The user created
		 */
		public function updateUser(User $user)
		{
		 // persist the user
		 $this->entityManager->persist($user);
		 $this->entityManager->flush();
		 // dispatch en event
		 $event = new UserPasswordChangedEvent($user);
		 $this->eventDispatcher->dispatch(UserPasswordChangedEvent::NAME, $event);
		 // return the user
		 return $user;
		}

    /**
     * Get the private communities of the given user.
     *
     * @param User $user
     * @return array
     */
    public function getPrivateCommunities(?User $user): array
    {
        if (is_null($user)) {
            return [];
        }
        if ($communities = $this->communityRepository->findByUser($user, true, null, CommunityUser::STATUS_ACCEPTED)) {
            return $communities;
        }
        return [];
    }

    public function getThreads(User $user): array
    {
        if ($threads = $this->messageRepository->findThreads($user)) {
            return $threads;
        }
        return [];
    }
 
 public function updateUserPasswordRequest(User $user)
 {
	 // persist the user
	 $this->entityManager->persist($user);
	 $this->entityManager->flush();
	 // dispatch en event
	 $event = new UserPasswordChangeAskedEvent($user);
	 $this->eventDispatcher->dispatch(UserPasswordChangeAskedEvent::NAME, $event);
	 // return the user
	 return $user;
 }
 
 public function updateUserPasswordConfirm(User $user)
 {
		// persist the user
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		// dispatch en event
		$event = new UserPasswordChangedEvent($user);
		$this->eventDispatcher->dispatch(UserPasswordChangedEvent::NAME, $event);
		// return the user
		return $user;
 }
}
