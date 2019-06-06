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

namespace App\Right\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\User\Entity\User;
use App\Geography\Entity\Territory;
use App\Right\Repository\RightRepository;
use App\Right\Entity\Right;
use App\Right\Entity\Role;
use App\Right\Repository\RoleRepository;
use App\Right\Entity\Permission;
use App\Right\Entity\UserRole;

/**
 * Permission manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PermissionManager
{
    private $entityManager;
    private $logger;
    private $rightRepository;
    private $roleRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, RightRepository $rightRepository, RoleRepository $roleRepository)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->rightRepository = $rightRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Check if a user has a permission on an right, eventually on a given territory
     *
     * @param Right $right
     * @param User|null $user
     * @param Territory|null $territory
     * @return void
     */
    public function userHasPermission(Right $right, ?User $user, Territory $territory=null): Permission
    {
        $permission = new Permission(1);
        $permission->setPermission(false);
        // if no user is passed we consider the basic user
        if (!$user instanceof User) {
            $user = new User();
            $userRole = new UserRole();
            $userRole->setUser($user);
            $userRole->setRole($this->roleRepository->find(Role::ROLE_USER));
            $user->addUserRole($userRole);
        }

        // we first check if the user is seated on the iron throne
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
            $permission->setPermission(true);
            return $permission;
        }

        // todo : maybe replace the following code by a DQL request...

        // we check if the user has a role that has the right to do the action
        foreach ($user->getUserRoles() as $userRole) {
            if (is_null($userRole->getTerritory()) || $userRole->getTerritory() == $territory) {
                if ($this->roleHasRight($userRole->getRole(), $right)) {
                    $permission->setPermission(true);
                    return $permission;
                }
            }
        }

        // we check if a the user has the specific right to do the action
        foreach ($user->getUserRights() as $userRight) {
            if (is_null($userRight->getTerritory()) || $userRight->getTerritory() == $territory) {
                if ($userRight->getRight()->getName() == $right->getName()) {
                    $permission->setPermission(true);
                    return $permission;
                } else {
                    foreach ($this->rightRepository->findChildren($userRight->getRight()) as $child) {
                        if ($child->getName() == $right->getName()) {
                            $permission->setPermission(true);
                            return $permission;
                        }
                    }
                }
            }
        }

        return $permission;
    }

    // check if a role has a right
    // recursive if the role has children
    private function roleHasRight(Role $role, Right $right)
    {
        foreach ($role->getRights() as $uright) {
            if ($uright->getName() == $right->getName()) {
                return true;
            } else {
                foreach ($this->rightRepository->findChildren($uright) as $child) {
                    if ($child->getName() == $right->getName()) {
                        return true;
                    }
                }
            }
        }
        $permission = false;
        // we check if the children of the role have the right
        foreach ($this->roleRepository->findChildren($role) as $child) {
            $permission = $this->roleHasRight($child, $right);
        }
        return $permission;
    }
}
