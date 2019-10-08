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
use DateTime;
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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\User\Event\UserUpdatedSelfEvent;

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
    private $encoder;
 
    /**
        * Constructor.
        *
        * @param EntityManagerInterface $entityManager
        * @param LoggerInterface $logger
        */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $dispatcher, RoleRepository $roleRepository, CommunityRepository $communityRepository, MessageRepository $messageRepository, UserPasswordEncoderInterface $encoder)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
        $this->communityRepository = $communityRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher = $dispatcher;
        $this->encoder = $encoder;
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
        // creation of the geotoken
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        $geoToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setGeoToken($geoToken);
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
        // update of the geotoken
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        $geoToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setGeoToken($geoToken);
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // dispatch en event
        $event = new UserUpdatedSelfEvent($user);
        $this->eventDispatcher->dispatch(UserUpdatedSelfEvent::NAME, $event);
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

    public function getThreadsDirectMessages(User $user): array
    {
        if ($threads = $this->messageRepository->findThreadsDirectMessages($user)) {
            $messages = [];
            foreach ($threads as $thread) {
                $currentMessage = [];
                // To Do : We support only one recipient at this time...
                $messages[] = [
                    'idMessage' => $thread->getId(),
                    'idRecipient' => ($user->getId() === $thread->getUser('user')->getId()) ? $thread->getRecipients()[0]->getUser('user')->getId() : $thread->getUser('user')->getId(),
                    'givenName' => ($user->getId() === $thread->getUser('user')->getId()) ? $thread->getRecipients()[0]->getUser('user')->getGivenName() : $thread->getUser('user')->getGivenName(),
                    'familyName' => ($user->getId() === $thread->getUser('user')->getId()) ? $thread->getRecipients()[0]->getUser('user')->getFamilyName() : $thread->getUser('user')->getFamilyName(),
                    'date' => ($thread->getLastMessage()===null) ? $thread->getCreatedDate() : $thread->getLastMessage()->getCreatedDate(),
                    'selected' => false
                ];
            }

            return $messages;
        }
        return [];
    }

    public function getThreadsCarpoolMessages(User $user): array
    {
        if ($threads = $this->messageRepository->findThreadsCarpoolMessages($user)) {
            $messages = [];
            foreach ($threads as $thread) {
                $currentMessage = [];
                // To Do : We support only one recipient at this time...
                $messages[] = [
                    'idMessage' => $thread->getId(),
                    'idRecipient' => ($user->getId() === $thread->getUser('user')->getId()) ? $thread->getRecipients()[0]->getUser('user')->getId() : $thread->getUser('user')->getId(),
                    'givenName' => ($user->getId() === $thread->getUser('user')->getId()) ? $thread->getRecipients()[0]->getUser('user')->getGivenName() : $thread->getUser('user')->getGivenName(),
                    'familyName' => ($user->getId() === $thread->getUser('user')->getId()) ? $thread->getRecipients()[0]->getUser('user')->getFamilyName() : $thread->getUser('user')->getFamilyName(),
                    'date' => ($thread->getLastMessage()===null) ? $thread->getCreatedDate() : $thread->getLastMessage()->getCreatedDate(),
                    'selected' => false
                ];
            }

            return $messages;
        }
        return [];
    }
    
    /**
       * User password change request.
       *
       * @param User $user
       * @return User
       */
    public function updateUserPasswordRequest(User $user)
    {
        $datetime = new DateTime();
        $time = $datetime->getTimestamp();
        // encoding of the password
        $pwdToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setPwdToken($pwdToken);
        // update of the geotoken
        $geoToken = $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt());
        $user->setGeoToken($geoToken);
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // dispatch en event
        $event = new UserPasswordChangeAskedEvent($user);
        $this->eventDispatcher->dispatch($event, UserPasswordChangeAskedEvent::NAME);
        // return the user
        return $user;
    }
 
    /**
       * User password change confirmation.
       *
       * @param User $user
       * @return User
       */
    public function updateUserPasswordConfirm(User $user)
    {
        $user->setPwdToken(null);
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // dispatch en event
        $event = new UserPasswordChangedEvent($user);
        $this->eventDispatcher->dispatch($event, UserPasswordChangedEvent::NAME);
        // return the user
        return $user;
    }
}
