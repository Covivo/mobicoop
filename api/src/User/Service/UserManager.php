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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Right\Repository\RoleRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;

/**
 * User manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserManager
{
    private $entityManager;
    private $roleRepository;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, RoleRepository $roleRepository)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
    }
    
    /**
     * Create a user.
     *
     * @param User $user    The user to create
     * @return User         The user created
     */
    public function createUser(User $user)
    {
        // default role : user registered full
        $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL);
        $userRole = new UserRole();
        $userRole->setRole($role);
        $user->addUserRole($userRole);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

}