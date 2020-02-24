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
use App\Carpool\Entity\Ad;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Service\AdManager;
use App\Community\Service\CommunityManager;
use App\Event\Service\EventManager;
use App\User\Entity\User;
use App\Geography\Entity\Territory;
use App\MassCommunication\Service\CampaignManager;
use App\RelayPoint\Service\RelayPointManager;
use App\Right\Repository\RightRepository;
use App\Right\Entity\Right;
use App\Right\Entity\Role;
use App\Right\Repository\RoleRepository;
use App\Right\Entity\Permission;
use App\Right\Entity\UserRole;
use App\Right\Exception\RightNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
    private $adManager;
    private $campaignManager;
    private $communityManager;
    private $eventManager;
    private $relayPointManager;
    private $tokenStorage;
    private $matchingRepository;

    /**
     * Constructor.
     *
     * @param RightRepository $rightRepository  (DI) Right repository
     * @param RoleRepository $roleRepository    (DI) Role repository
     */
    public function __construct(
        RightRepository $rightRepository,
        RoleRepository $roleRepository,
        AdManager $adManager,
        CampaignManager $campaignManager,
        CommunityManager $communityManager,
        EventManager $eventManager,
        RelayPointManager $relayPointManager,
        TokenStorageInterface $tokenStorage,
        MatchingRepository $matchingRepository
    ) {
        $this->rightRepository = $rightRepository;
        $this->roleRepository = $roleRepository;
        $this->adManager = $adManager;
        $this->campaignManager = $campaignManager;
        $this->communityManager = $communityManager;
        $this->eventManager = $eventManager;
        $this->relayPointManager = $relayPointManager;
        $this->tokenStorage = $tokenStorage;
        $this->matchingRepository = $matchingRepository;
    }

    /**
     * Check if a requester can check a permission on an right
     *
     * @param UserInterface $requester  The requester (an app or a user)
     * @param UserInterface|null $user  The related user
     * @return bool
     */
    public function canCheckPermission(UserInterface $requester, ?UserInterface $user=null)
    {
        // we first check if the user has the right to check a permission !
        $rightToCheck = $this->rightRepository->findByName('check_permission');
        $authorized = false;
        if ($requester instanceof User) {
            $authorized = $this->userHasPermission($rightToCheck, $requester)->isGranted();
        } elseif ($requester instanceof App) {
            $authorized = $this->appHasPermission($rightToCheck, $requester)->isGranted();
        }
        if ($authorized) {
            return true;
        }
        
        // here the user isn't authorized, we check if he has the right to check for its own objects
        $rightToCheck = $this->rightRepository->findByName('check_permission_self');
        $authorized = false;
        if ($requester instanceof User) {
            $authorized = $this->userHasPermission($rightToCheck, $requester)->isGranted();
        } elseif ($requester instanceof App) {
            $authorized = $this->appHasPermission($rightToCheck, $requester)->isGranted();
        }
        if ($authorized) {
            if (!is_null($user)) {
                /**
                 * We check if the requester is the user
                 *
                 * @var User|App $requester
                 * @var User|App $user
                 */
                if ($requester->getId() == $user->getId()) {
                    return true;
                }
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a requester has a permission on an right, eventually on a given territory, eventually on a related object user
     *
     * @param string                $rightName The name of the right to check
     * @param UserInterface|null    $requester The requester (an app or a user)
     * @param Territory|null        $territory The territory
     * @param int|null              $id        The id of the related object
     * @param object|null           $object    The related object
     * @return bool
     */
    public function checkPermission(string $rightName, ?UserInterface $requester=null, ?Territory $territory=null, ?int $id = null, ?object $object = null)
    {
        if (!$right = $this->rightRepository->findByName($rightName)) {
            throw new RightNotFoundException('Right ' . $rightName . ' not found');
        }
        if (is_null($requester)) {
            $requester = new User();
            $role = $this->roleRepository->find(Role::ROLE_USER);
            $userRole = new UserRole();
            $userRole->setRole($role);
            $requester->addUserRole($userRole);
        }
        if ($requester instanceof User) {
            return $this->userHasPermission($right, $requester, $territory, $id, $object)->isGranted();
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
     * @param object|null  $object      The related object
     * @return Permission
     */
    public function userHasPermission(Right $right, ?User $user, ?Territory $territory=null, ?int $id=null, ?object $object=null): Permission
    {
        if (is_null($user)) {
            // no user specified, we check who is the requester
            $requester = $this->tokenStorage->getToken()->getUser();
            if ($requester instanceof App) {
                // after refactor of the client token comment/uncomment the following
                // ---
                $user = $requester;
            // it's an app, we will check the permission for a basic (anonymous) user
                // $user = new User();
                // $role = $this->roleRepository->find(Role::ROLE_USER);
                // $userRole = new UserRole();
                // $userRole->setRole($role);
                // $user->addUserRole($userRole);
                // ---
            } else {
                // "real" user, we use it
                $user = $requester;
            }
        }

        $permission = new Permission(1);
        $permission->setGranted(false);

        // we first check if the user is seated on the iron throne
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
            $permission->setGranted(true);
            return $permission;
        }
        
        // we search all the roles of the user (its direct roles and its children)
        // we also keep the territory, even if we don't really use it yet !
        // note : if a role is associated with a territory, its children are also associated with it
        $roles = [];
        foreach ($user->getUserRoles() as $userRole) {
            if (is_null($userRole->getTerritory()) || $userRole->getTerritory() == $territory) {
                $roles[] = [
                    'role' => $userRole->getRole(),
                    'territory' => $territory
                ];
                foreach ($this->getRoleChildren($userRole->getRole()) as $role) {
                    $roles[] = [
                        'role' => $role,
                        'territory' => $territory
                    ];
                }
            }
        }
        
        if ($this->rightInRoles($right, $roles, $user->getId(), $id, $object)) {
            $permission->setGranted(true);
            return $permission;
        }
        
        // we check if the user has this specific right
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
     * Get the children of a role.
     *
     * @param Role $role    The role
     * @return array        The children
     */
    private function getRoleChildren(Role $role)
    {
        $achildren = [];
        $children = $this->roleRepository->findChildren($role);
        foreach ($children as $child) {
            $achildren[] = $child;
        }
        foreach ($children as $child) {
            $children = $this->getRoleChildren($child);
            foreach ($children as $cchild) {
                $achildren[] = $cchild;
            }
        }
        return $achildren;
    }

    /**
     * Check if a right is in given roles
     *
     * @param Right $right              The right
     * @param array $roles              The array of roles
     * @param integer|null $userId      The user id
     * @param integer|null $id          The related object id
     * @param object|null $object       The related object
     * @return void
     */
    private function rightInRoles(Right $right, array $roles, ?int $userId=null, ?int $id=null, ?object $object=null)
    {
        // echo "check right " . $right->getName() . "\n";
        foreach ($right->getRoles() as $rightRole) {
            // echo "check rightrole " . $rightRole->getName() . "\n";
            foreach ($roles as $role) {
                // echo "check role " . $role['role']->getName() . "\n";
                if ($role['role']->getId() == $rightRole->getId()) {
                    // common role found
                    if ($this->isOwner($right, $userId, $id, $object)) {
                        return true;
                    }
                    return false;
                }
            }
        }
        // no common role found => we try the parents of the right
        foreach ($right->getParents() as $parent) {
            if ($this->rightInRoles($parent, $roles, $userId, $id, $object)) {
                return true;
            }
        }
        return false;
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

        // we search all the roles of the user (its direct roles and its children)
        // we also keep the territory, even if we don't really use it yet !
        // note : if a role is associated with a territory, its children are also associated with it
        $roles = [];
        foreach ($app->getRoleObjects() as $role) {
            $roles[] = [
                'role' => $role
            ];
            foreach ($this->getRoleChildren($role) as $child) {
                $roles[] = [
                    'role' => $child
                ];
            }
        }
        
        if ($this->rightInRoles($right, $roles)) {
            $permission->setGranted(true);
            return $permission;
        }

        return $permission;
    }

    /**
     * Check if two rights match
     * Recursive if the current right to check has parents
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
            // we check if the parents of the right have the right
            foreach ($currentRight->getParents() as $parentRight) {
                if ($this->rightHasRight($rightToCheck, $parentRight, $userId, $id)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get the users's permissions.
     * We limit to the first level of permissions.
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
        }
        ksort($permissions);
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
     * @param object|null $object    The object
     * @return boolean
     */

    private function isOwner(Right $right, ?int $requesterId, ?int $objectId, ?object $object)
    {
        // if the right has no related object, it means we don't need to check for an ownership
        if (is_null($right->getObject())) {
            return true;
        }
        if (is_null($objectId)) {
            return false;
        }
        switch ($right->getObject()) {
            case "ad":
                if ($ad = $this->adManager->getAdForPermission($objectId)) {
                    return $ad->getUserId() == $requesterId;
                }
                break;
            case "canCreateAsk()":
                return self::canCreateAsk($objectId, $requesterId);
            case "campaign":
                return $this->campaignManager->getCampaignOwner($objectId) == $requesterId;
            case "community":
                if ($community = $this->communityManager->getCommunity($objectId)) {
                    return $community->getUser()->getId() == $requesterId;
                }
                break;
            case "event":
                if ($event = $this->eventManager->getEvent($objectId)) {
                    return $event->getUser()->getId() == $requesterId;
                }
                break;
            case "relaypoint":
                if ($relayPoint = $this->relayPointManager->getRelayPoint($objectId)) {
                    return $relayPoint->getUser()->getId() == $requesterId;
                }
                break;
            case "user":
                return $objectId === $requesterId;
            case "communityManaged()":
                return self::communityManaged($objectId);
        }
        return false;
    }

    private function communityManaged($communityId)
    {
        return true;
    }

    private function canCreateAsk(int $matchingId, int $requesterId)
    {
        // we check that the user id provided in the request is one of the matching proposals owners
        $matching = $this->matchingRepository->find($matchingId);
        if ($matching->getProposalOffer()->getUser()->getId() == $requesterId || $matching->getProposalRequest()->getUser()->getId() == $requesterId) {
            return true;
        }
        return false;
    }
}
