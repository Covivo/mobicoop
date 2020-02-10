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

use App\App\Entity\App;
use App\User\Entity\User;
use App\Geography\Entity\Territory;
use App\Right\Repository\RightRepository;
use App\Right\Entity\Right;
use App\Right\Entity\Role;
use App\Right\Repository\RoleRepository;
use App\Right\Entity\Permission;
use App\Right\Exception\RightNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Permission manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PermissionManager
{
    private $rightRepository;
    private $roleRepository;

    /**
     * Constructor.
     *
     * @param RightRepository $rightRepository  (DI) Right repository
     * @param RoleRepository $roleRepository    (DI) Role repository
     */
    public function __construct(RightRepository $rightRepository, RoleRepository $roleRepository)
    {
        $this->rightRepository = $rightRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Check if a requester has a permission on an right, eventually on a given territory, eventually on a related object user
     *
     * @param Right $right              The right to check
     * @param UserInterface $requester  The requester (an app or a user)
     * @param Territory|null $territory The territory
     * @param int|null  $id             The id of the related object
     * @return bool
     */
    public function checkPermission(string $rightName, UserInterface $requester, ?Territory $territory=null, ?int $id = null)
    {
        if (!$right = $this->rightRepository->findByName($rightName)) {
            throw new RightNotFoundException('Right ' . $rightName . ' not found');
        }
        if ($requester instanceof User) {
            return $this->userHasPermission($right, $requester, $territory, $id)->isGranted();
        } elseif ($requester instanceof App) {
            return $this->appHasPermission($right, $requester)->isGranted();
        }
        return false;
    }

    /**
     * Check if a user has a permission on an right, eventually on a given territory, eventually on a related object user
     *
     * @param Right $right              The right to check
     * @param User $user                The user to check the right for
     * @param Territory|null $territory The territory
     * @param int|null  $id             The id of the related object
     * @return Permission
     */
    public function userHasPermission(Right $right, User $user, ?Territory $territory=null, ?int $id=null): Permission
    {
        $permission = new Permission(1);
        $permission->setGranted(false);

        // we first check if the user is seated on the iron throne
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
            $permission->setGranted(true);
            return $permission;
        }

        // we check if the user has a role that has the right to do the action
        foreach ($user->getUserRoles() as $userRole) {
            if (is_null($userRole->getTerritory()) || $userRole->getTerritory() == $territory) {
                if ($this->roleHasRight($userRole->getRole(), $right, $user->getId(), $id)) {
                    $permission->setGranted(true);
                    return $permission;
                }
            }
        }

        // we check if the user has the specific right to do the action
        foreach ($user->getUserRights() as $userRight) {
            if (is_null($userRight->getTerritory()) || $userRight->getTerritory() == $territory) {
                if ($this->rightHasRight($right, $userRight->getRight(), $user->getId(), $id)) {
                    $permission->setGranted(true);
                    return $permission;
                }
            }
        }

        return $permission;
    }

    /**
     * Check if an app has a permission on an right
     *
     * @param Right $right  The right to check
     * @param App $app      The app
     * @return Permission
     */
    public function appHasPermission(Right $right, App $app): Permission
    {
        $permission = new Permission(1);
        $permission->setGranted(false);

        // we first check if the app is seated on the iron throne
        if (in_array('ROLE_SUPER_ADMIN', $app->getRoles())) {
            // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
            $permission->setGranted(true);
            return $permission;
        }

        // we check if the app has a role that has the right to do the action
        foreach ($app->getRoleObjects() as $role) {
            if ($this->roleHasRight($role, $right)) {
                $permission->setGranted(true);
                return $permission;
            }
        }
        return $permission;
    }

    //
    /**
     * Check if a role has a right
     * Recursive if the role has children
     *
     * @param Role $role            The role
     * @param Right $right          The right
     * @param integer|null $userId  The user id
     * @param integer|null $id      The id of the related object if needed
     * @return void
     */
    private function roleHasRight(Role $role, Right $right, ?int $userId=null, ?int $id=null)
    {
        foreach ($role->getRights() as $currentRight) {
            if ($this->rightHasRight($right, $currentRight, $userId, $id)) {
                return true;
            }
        }
        // we check if the children of the role have the right
        foreach ($this->roleRepository->findChildren($role) as $child) {
            if ($this->roleHasRight($child, $right, $userId, $id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if two rights match
     * Recursive if the current right to check has children
     *
     * @param Right $rightToCheck   The right to check
     * @param Right $currentRight   The current right
     * @param integer|null $userId  The user id
     * @param integer|null $id      The id of the related object if needed
     * @return void
     */
    private function rightHasRight(Right $rightToCheck, Right $currentRight, ?int $userId=null, ?int $id=null)
    {
        if ($currentRight->getName() == $rightToCheck->getName()) {
            if (!$this->isOwner($rightToCheck, $userId, $id)) {
                return false;
            }
            return true;
        } else {
            // we check if the children of the right have the right
            foreach ($this->rightRepository->findChildren($currentRight) as $childRight) {
                if ($this->rightHasRight($rightToCheck, $childRight, $userId, $id)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get the users's permissions.
     *
     * @param User $user
     * @return Array
     */
    public function getUserPermissions(User $user, $territory=null): array
    {
        $permissions = [];
        // we search the rights of each role of the user (and its subsequent roles)
        foreach ($user->getUserRoles() as $userRole) {
            $this->getRoleRights($userRole->getRole(), $userRole->getTerritory(), $permissions);
        }
        // we search the rights directly granted to the user
        foreach ($user->getUserRights() as $userRight) {
            if ($userRight->getTerritory()) {
                $permissions[$userRight->getRight()->getName()][] = $userRight->getTerritory()->getId();
            } else {
                $permissions[$userRight->getRight()->getName()] = [];
            }
            foreach ($this->rightRepository->findChildren($userRight->getRight()) as $child) {
                if ($userRight->getTerritory()) {
                    $permissions[$child->getName()][] = $userRight->getTerritory()->getId();
                } else {
                    $permissions[$child->getName()] = [];
                }
            }
        }
        return $permissions;
    }

    // get the right of a given role (and the rights of its children)
    private function getRoleRights(Role $role, Territory $territory=null, array &$permissions)
    {
        foreach ($role->getRights() as $right) {
            if ($territory) {
                $permissions[$right->getName()][] = $territory->getId();
            } else {
                $permissions[$right->getName()] = [];
            }
            foreach ($this->rightRepository->findChildren($right) as $child) {
                if ($territory) {
                    $permissions[$child->getName()][] = $territory->getId();
                } else {
                    $permissions[$child->getName()] = [];
                }
            }
        }
        foreach ($this->roleRepository->findChildren($role) as $child) {
            $this->getRoleRights($child, $territory, $permissions);
        }
    }
    
    /**
     * Check if a requester is the owner of an object related to a right
     *
     * @param Right $right              The right
     * @param integer|null $requesterId The requester
     * @param integer|null $objectId    The object id
     * @return boolean
     */

    private function isOwner(Right $right, ?int $requesterId, ?int $objectId)
    {
        // if the right has no related object, it means we don't need to check for an ownership
        if (is_null($right->getObject())) {
            return true;
        }
        switch ($right->getObject()) {
            case "user":
                return $objectId === $requesterId;
        }
        return false;
    }
}
