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
 */

namespace App\Auth\Service;

use App\App\Entity\App;
use App\Auth\Entity\Permission;
use App\Auth\Entity\Right;
use App\Auth\Entity\Role;
use App\Auth\Entity\UserRole;
use App\Auth\Exception\AuthItemNotFoundException;
use App\Auth\Repository\AuthItemRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Service\AdManager;
use App\Community\Service\CommunityManager;
use App\Event\Service\EventManager;
use App\Geography\Entity\Territory;
use App\MassCommunication\Service\CampaignManager;
use App\RelayPoint\Service\RelayPointManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Permission manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PermissionManager
{
    private $authItemRepository;
    private $adManager;
    private $campaignManager;
    private $communityManager;
    private $eventManager;
    private $relayPointManager;
    private $tokenStorage;
    private $matchingRepository;

    /**
     * Constructor.
     */
    public function __construct(
        AuthItemRepository $authItemRepository,
        AdManager $adManager,
        CampaignManager $campaignManager,
        CommunityManager $communityManager,
        EventManager $eventManager,
        RelayPointManager $relayPointManager,
        TokenStorageInterface $tokenStorage,
        MatchingRepository $matchingRepository
    ) {
        $this->authItemRepository = $authItemRepository;
        $this->adManager = $adManager;
        $this->campaignManager = $campaignManager;
        $this->communityManager = $communityManager;
        $this->eventManager = $eventManager;
        $this->relayPointManager = $relayPointManager;
        $this->tokenStorage = $tokenStorage;
        $this->matchingRepository = $matchingRepository;
    }

    /**
     * Check if a requester has a permission on an item.
     * The requester is retrieved from the connection token.
     *
     * @param string $itemName The name of the item to check
     * @param array  $params   The params associated with the right
     */
    public function hasPermission(string $itemName, array $params = []): bool
    {
        if (is_null($this->tokenStorage->getToken())) {
            // anonymous connection => any right should be denied, as allowed resources won't be checked for permissions
            return false;
        }
        if (!$item = $this->authItemRepository->findByName($itemName)) {
            throw new AuthItemNotFoundException('Auth item '.$itemName.' not found');
        }

        $requester = $this->tokenStorage->getToken()->getUser();

        return false;
    }

    /**
     * Check if a requester can check a permission on an right.
     *
     * @param UserInterface      $requester The requester (an app or a user)
     * @param null|UserInterface $user      The related user
     */
    public function canCheckPermission(UserInterface $requester, ?UserInterface $user = null): bool
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
                 * We check if the requester is the user.
                 *
                 * @var App|User $requester
                 * @var App|User $user
                 */
                if ($requester->getId() == $user->getId()) {
                    return true;
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Check if a requester has a permission on an right, eventually on a given territory, eventually on a related object user.
     *
     * @param string             $rightName The name of the right to check
     * @param null|UserInterface $requester The requester (an app or a user)
     * @param null|Territory     $territory The territory
     * @param null|int           $id        The id of the related object
     * @param null|object        $object    The related object
     */
    public function checkPermission(string $rightName, ?UserInterface $requester = null, ?Territory $territory = null, ?int $id = null, ?object $object = null): bool
    {
        if (!$right = $this->rightRepository->findByName($rightName)) {
            throw new RightNotFoundException('Right '.$rightName.' not found');
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
        }
        if ($requester instanceof App) {
            return $this->appHasPermission($right, $requester)->isGranted();
        }

        return false;
    }

    /**
     * Check if a user has a permission on an right, eventually on a given territory, eventually on a related object user.
     *
     * @param Right          $right     The right to check
     * @param User           $user      The user to check the right for
     * @param null|Territory $territory The territory
     * @param null|int       $id        The id of the related object
     * @param null|object    $object    The related object
     */
    public function userHasPermission(Right $right, ?User $user, ?Territory $territory = null, ?int $id = null, ?object $object = null): Permission
    {
        if (is_null($user)) {
            // no user specified, we check who is the requester
            $requester = $this->tokenStorage->getToken()->getUser();
            if ($requester instanceof App) {
                // after refactor of the client token comment/uncomment the following
                // ---
                $app = $requester;
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

        if (isset($app)) {
            // I'm an app
            $permission = new Permission(1);
            $permission->setGranted(false);
            // we first check if the user is seated on the iron throne
            if (in_array('ROLE_SUPER_ADMIN', $app->getRoles())) {
                // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
                $permission->setGranted(true);

                return $permission;
            }

            // we search all the roles of the user (its direct roles and its children)
            // we also keep the territory, even if we don't really use it yet !
            // note : if a role is associated with a territory, its children are also associated with it
            $roles = [];
            /**
             * @var Role $role
             */
            foreach ($app->getRoleObjects() as $role) {
                $roles[] = [
                    'role' => $role,
                ];
                foreach ($this->getRoleChildren($role) as $childRole) {
                    $roles[] = [
                        'role' => $childRole,
                    ];
                }
            }

            if ($this->rightInRoles($right, $roles, $app->getId(), $id, $object)) {
                $permission->setGranted(true);

                return $permission;
            }
        } else {
            // I'm a real user
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
                        'territory' => $territory,
                    ];
                    foreach ($this->getRoleChildren($userRole->getRole()) as $role) {
                        $roles[] = [
                            'role' => $role,
                            'territory' => $territory,
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
        }

        return $permission;
    }

    /**
     * Get the allowed territories for a user and a right.
     *
     * @param User   $user      The user
     * @param string $rightName The name of the right
     *
     * @return array The array of territories where the user is authorized (empty array if the user is authorized on any territory)
     */
    public function getTerritoriesForUserAndRight(User $user, string $rightName): array
    {
        if (!$right = $this->rightRepository->findByName($rightName)) {
            throw new RightNotFoundException('Right '.$rightName.' not found');
        }

        $territories = [];

        // we first check if the user is seated on the iron throne
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
            return $territories;
        }

        // we check if the user has a role that is authorized for the right
        foreach ($user->getUserRoles() as $userRole) {
            foreach ($right->getRoles() as $rightRole) {
                if ($userRole->getRole()->getId() == $rightRole->getId()) {
                    // common role found
                    if (is_null($userRole->getTerritory())) {
                        // no territory is assigned to the role => all territories allowed !
                        return [];
                    }
                    // a specific territory is assigned => we add it to the final list
                    $territories[] = $userRole->getTerritory()->getId();
                } else {
                    // no common role found => we check the children of the role
                    foreach ($this->getRoleChildren($userRole->getRole()) as $role) {
                        if ($role->getId() == $rightRole->getId()) {
                            // common role found in children
                            if (is_null($userRole->getTerritory())) {
                                // no territory is assigned to the parent role => all territories allowed !
                                return [];
                            }
                            // a specific territory is assigned => we add it to the final list
                            $territories[] = $userRole->getTerritory()->getId();
                        }
                    }
                }
            }
        }

        // we check if the user has the specific right
        foreach ($user->getUserRights() as $userRight) {
            if ($userRight->getRight()->getId() == $right->getId()) {
                // the user has the specific right
                if (is_null($userRight->getTerritory())) {
                    // no territory is assigned to the role => all territories allowed !
                    return [];
                }
                // a specific territory is assigned => we add it to the final list
                $territories[] = $userRight->getTerritory()->getId();
            }
        }

        return array_values($territories);
    }

    // /**
    //  * Check if a right is in given roles
    //  *
    //  * @param Right $right              The right
    //  * @param array $roles              The array of roles
    //  * @param integer|null $userId      The user id
    //  * @param integer|null $id          The related object id
    //  * @param object|null $object       The related object
    //  * @return void
    //  */
    // private function rightInRoles(Right $right, array $roles, ?int $userId=null, ?int $id=null, ?object $object=null)
    // {
    //     //  echo "check right " . $right->getName() . "\n";
    //     foreach ($right->getRoles() as $rightRole) {
    //         //  echo "check rightrole " . $rightRole->getName() . "\n";
    //         foreach ($roles as $role) {
    //             //  echo "check role " . $role['role']->getName() . "\n";
    //             if ($role['role']->getId() == $rightRole->getId()) {
    //                 // common role found
    //                 if ($this->isOwner($right, $userId, $id, $object)) {
    //                     return true;
    //                 }
    //                 return false;
    //             }
    //         }
    //     }

    //     return false;
    // }

    /**
     * Check if an app has a permission on an right.
     *
     * @param Right $right The right to check
     * @param App   $app   The app
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
                'role' => $role,
            ];
            foreach ($this->getRoleChildren($role) as $child) {
                $roles[] = [
                    'role' => $child,
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
     * Get the users's permissions.
     * We limit to the first level of permissions.
     *
     * @param null|mixed $territory
     */
    public function getUserPermissions(User $user, $territory = null): array
    {
        return [];
        // we search the rights of each role of the user (and its subsequent roles)
        // foreach ($user->getUserRoles() as $userRole) {
        //     $this->getRoleRights($userRole->getRole(), $userRole->getTerritory(), $permissions);
        // }
        // // we search the rights directly granted to the user
        // foreach ($user->getUserRights() as $userRight) {
        //     if ($userRight->getTerritory()) {
        //         $permissions[$userRight->getRight()->getName()][] = $userRight->getTerritory()->getId();
        //     } else {
        //         $permissions[$userRight->getRight()->getName()] = [];
        //     }
        // }
        // ksort($permissions);
    }

    /**
     * Check if a right is in given roles.
     *
     * @param Right $right The right
     * @param array $roles The array of roles (may be associated with a territory)
     */
    private function rightInRoles(Right $right, array $roles): bool
    {
        // first we check if the right is directly associated with the role
        foreach ($right->getRoles() as $rightRole) {
            // @var Role $rightRole
            foreach ($roles as $role) {
                if ($role['role']->getId() == $rightRole->getId()) {
                    // common role found, we check if the
                    if ($this->isOwner($right, $userId, $id, $object)) {
                        return true;
                    }

                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Get the children of a role.
     *
     * @param Role $role The role
     *
     * @return array The children
     */
    private function getRoleChildren(Role $role): array
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
     * Check if two rights match
     * Recursive if the current right to check has parents.
     *
     * @param Right    $rightToCheck The right to check
     * @param Right    $currentRight The current right
     * @param null|int $userId       The user id
     * @param null|int $id           The id of the related object if needed
     */
    private function rightHasRight(Right $rightToCheck, Right $currentRight, ?int $userId = null, ?int $id = null): bool
    {
        if ($currentRight->getName() == $rightToCheck->getName()) {
            if (!$this->isOwner($rightToCheck, $userId, $id)) {
                return false;
            }

            return true;
        }
        // we check if the parents of the right have the right
        foreach ($currentRight->getParents() as $parentRight) {
            if ($this->rightHasRight($rightToCheck, $parentRight, $userId, $id)) {
                return true;
            }
        }

        return false;
    }

    // get the right of a given role (and the rights of its children)
    private function getRoleRights(Role $role, Territory $territory = null, array & $permissions)
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
     * Check if a requester is the owner of an object related to a right.
     *
     * @param Right       $right       The right
     * @param null|int    $requesterId The requester
     * @param null|int    $objectId    The object id
     * @param null|object $object      The object
     */
    private function isOwner(Right $right, ?int $requesterId, ?int $objectId, ?object $object): bool
    {
        // if the right has no related object, it means we don't need to check for an ownership
        if (is_null($right->getObject())) {
            return true;
        }
        // if (is_null($objectId)) {
        //     return false;
        // }
        // var_dump($right->getObject());
        switch ($right->getObject()) {
            case 'ad':
                if ($ad = $this->adManager->getAdForPermission($objectId)) {
                    return $ad->getUserId() == $requesterId;
                }

                break;

            case 'canCreateAsk()':
                return self::canCreateAsk($objectId, $requesterId);

            case 'campaign':
                return $this->campaignManager->getCampaignOwner($objectId) == $requesterId;

            case 'community':
                if ($community = $this->communityManager->getCommunity($objectId)) {
                    return $community->getUser()->getId() == $requesterId;
                }

                break;

            case 'event':
                if ($event = $this->eventManager->getEvent($objectId)) {
                    return $event->getUser()->getId() == $requesterId;
                }

                break;

            case 'relaypoint':
                if ($relayPoint = $this->relayPointManager->getRelayPoint($objectId)) {
                    return $relayPoint->getUser()->getId() == $requesterId;
                }

                break;

            case 'user':
                return $objectId === $requesterId;

            case 'communityManaged()':
                return self::communityManaged($right, $requesterId, $objectId);

            case 'message()':
                return $objectId === $requesterId;
        }

        return false;
    }

    private function communityManaged(Right $right, int $requesterId, ?int $communityId = null)
    {
        // To Do... all the community cases
        switch ($right->getName()) {
            case 'community_create':
            case 'community_private_create':
                return true;

                break;

            default:
                return false;
        }
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
