<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\Permission;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Exception\AuthItemNotFoundException;
use App\Auth\Interfaces\AuthRuleInterface;
use App\Auth\Repository\AuthItemRepository;
use App\Auth\Repository\UserAuthAssignmentRepository;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Auth manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AuthManager
{
    private $authItemRepository;
    private $userAuthAssignmentRepository;
    private $tokenStorage;

    private $user;
    private $userManager;
    private $modules;

    /**
     * Constructor.
     */
    public function __construct(
        AuthItemRepository $authItemRepository,
        UserAuthAssignmentRepository $userAuthAssignmentRepository,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
        array $modules
    ) {
        $this->authItemRepository = $authItemRepository;
        $this->userAuthAssignmentRepository = $userAuthAssignmentRepository;
        $this->tokenStorage = $tokenStorage;
        $this->user = null;
        $this->userManager = $userManager;
        $this->modules = $modules;
    }

    /**
     * Set the user for whom we want to check the authorization.
     * /!\ useful only for specific case like token refresh /!\.
     *
     * @param User $user The user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Check if a requester has an authorization on an item.
     * The requester is retrieved from the connection token.
     *
     * @param string $itemName The name of the item to check
     * @param array  $params   The params associated with the item
     *
     * @return bool
     */
    public function isAuthorized(string $itemName, array $params = [])
    {
        if (is_null($this->tokenStorage->getToken())) {
            // anonymous connection => any right should be denied, as allowed resources won't be checked for permissions
            return false;
        }
        if (!$item = $this->authItemRepository->findByName($itemName)) {
            throw new AuthItemNotFoundException('Auth item '.$itemName.' not found');
        }

        // we get the requester
        $requester = $this->tokenStorage->getToken()->getUser();

        if (is_string($requester)) {
            // the requester could contain only the id under certain circumstances (eg. refresh token), we check if the user was set by another way
            if ($this->user instanceof User) {
                $requester = $this->user;
            } else {
                // we should not authorize
                return false;
            }
        }

        // check if the item is authorized for the requester
        return $this->isAssigned($requester, $item, $params);
    }

    /**
     * Check if a requester has an authorization on an item.
     * The requester is passed in arguments.
     * Used for api inner checks.
     *
     * @param User   $requester The requester
     * @param string $itemName  The name of the item to check
     * @param array  $params    The params associated with the item
     *
     * @return bool
     */
    public function isInnerAuthorized(User $requester, string $itemName, array $params = [])
    {
        if (!$item = $this->authItemRepository->findByName($itemName)) {
            throw new AuthItemNotFoundException('Auth item '.$itemName.' not found');
        }

        // check if the item is authorized for the requester
        return $this->isAssigned($requester, $item, $params);
    }

    /**
     * Get the allowed territories to the current requester for an auth item.
     *
     * @param string $itemName The name of the item to check
     *
     * @return array The array of territories where the requester is authorized (empty array if the requester is authorized on any territory)
     */
    public function getTerritoriesForItem(string $itemName)
    {
        if (!$item = $this->authItemRepository->findByName($itemName)) {
            throw new AuthItemNotFoundException('Auth item '.$itemName.' not found');
        }

        // we get the requester
        if (!is_null($this->user)) {
            $requester = $this->user;
        } else {
            $requester = $this->tokenStorage->getToken()->getUser();
        }

        $territories = [];

        // we search the territories
        $this->getTerritories($requester, $item, $territories);

        if (in_array('all', $territories)) {
            $territories = [];
        }
        $territories = array_unique($territories);
        sort($territories);

        return $territories;
    }

    /**
     * Check if a requester has an authorization on an item, and returns a Permission object.
     * The requester is retrieved from the connection token.
     *
     * @param string $itemName The name of the item to check
     * @param array  $params   The params associated with the item
     *
     * @return Permission The permission
     */
    public function getPermissionForAuthItem(string $itemName, array $params = [])
    {
        $permission = new Permission(1);
        $permission->setGranted($this->isAuthorized($itemName, $params));

        return $permission;
    }

    /**
     * Return the assigned AuthItem of the current user.
     *
     * @param null|int  $type   Limit to this type af Auth Item
     * @param null|bool $withId If set to true, return also ROLE_ID
     *
     * @return array The auth items
     */
    public function getAuthItems(?int $type = null, bool $withId = false)
    {
        if (is_null($type)) {
            $type = AuthItem::TYPE_ITEM;
        }

        $authItems = [];

        // we get the requester
        if (!is_null($this->user)) {
            $requester = $this->user;
        } else {
            $requester = $this->tokenStorage->getToken()->getUser();
        }

        if ($userAssignments = $this->userAuthAssignmentRepository->findByUser($requester)) {
            foreach ($userAssignments as $userAssignment) {
                if ($userAssignment->getAuthItem()->getType() == $type) {
                    // maybe we will need some rule checking, we initialize the control value
                    $rulesChecked = true;
                    // for some special items, we also need to check the rule (eg. "manage" action which need the corresponding module to be enabled)
                    if (AuthItem::TYPE_ITEM == $type && $this->checkSpecialItem($userAssignment->getAuthItem())) {
                        // check the associated rule
                        $rulesChecked = $this->checkRule($requester, $userAssignment->getAuthItem(), $this->modules);
                    }
                    if ($rulesChecked) {
                        if ($withId) {
                            $authItems[] = [
                                'id' => $userAssignment->getAuthItem(),
                                'name' => $userAssignment->getAuthItem()->getName(),
                            ];
                        } else {
                            $authItems[] = $userAssignment->getAuthItem()->getName();
                        }
                    }
                }
                $this->getChildrenNames($userAssignment->getAuthItem(), $type, $authItems, $withId);
            }
        }

        return $withId ? array_map('unserialize', array_unique(array_map('serialize', $authItems))) : array_unique($authItems);
    }

    /**
     * Get roles granted for the current user for create others user.
     *
     * @param User $user The current user
     *
     * @return null|AuthItem
     */
    public function getAuthItemsGrantedForCreation(User $user)
    {
        // All the roles of the current user, set true for get the AuthItem, not just the name
        $rolesUser = $this->getAuthItems(AuthItem::TYPE_ROLE, true);
        // Array we return, contain the roles current user can create
        $rolesGranted = [];

        foreach ($rolesUser as $role) {
            $rolesGranted = $this->checkRolesGrantedForRole($role, $rolesGranted);
        }

        return $rolesGranted;
    }

    /**
     * Check if a requester is assigned an auth item (recursive).
     *
     * @param UserInterface $requester The requester
     * @param AuthItem      $authItem  The auth item
     * @param array         $params    The params needed to check the authorization (will be passed to the rule if it exists)
     *
     * @return bool True if the requester is assigned the item, false either
     */
    private function isAssigned(UserInterface $requester, AuthItem $authItem, array $params)
    {
        // we check if there's a rule
        if ($this->checkRule($requester, $authItem, $params)) {
            // we check if the item is directly assigned to the user
            if ($requester instanceof User) {
                if ($this->userAuthAssignmentRepository->findByAuthItemAndUser($authItem, $requester)) {
                    // the item is found
                    return true;
                }
                // the item is not assigned, we check its parents
                foreach ($authItem->getParents() as $parent) {
                    if ($this->isAssigned($requester, $parent, $params)) {
                        return true;
                    }
                }
            } elseif ($requester instanceof App) {
                if (in_array($authItem, $requester->getAuthItems())) {
                    // the item is found
                    return true;
                }
                // the item is not assigned, we check its parents
                foreach ($authItem->getParents() as $parent) {
                    if ($this->isAssigned($requester, $parent, $params)) {
                        return true;
                    }
                }
            }
        }

        // not assigned !
        return false;
    }

    /**
     * Check if there's a rule associated with an auth item, and execute it.
     *
     * @param UserInterface $requester The requester
     * @param AuthItem      $authItem  The auth item
     * @param array         $params    The params needed to check the authorization
     *
     * @return bool True if there's no rule or if the rule is validated, false either
     */
    private function checkRule(UserInterface $requester, AuthItem $authItem, array $params)
    {
        if (is_null($authItem->getAuthRule())) {
            // no rule associated, we're good !
            return true;
        }
        // at this point a rule is associated, we need to execute it
        $authRuleName = '\\App\\Auth\\Rule\\'.$authItem->getAuthRule()->getName();

        /**
         * @var AuthRuleInterface $authRule
         */
        $authRule = new $authRuleName();

        return $authRule->execute($requester, $authItem, $params);
    }

    /**
     * Create an array of allowed territories for an item (recursive).
     * For now just for Users, not Apps.
     *
     * @param UserInterface $requester   The requester
     * @param AuthItem      $authItem    The authItem to check
     * @param array         $territories The territories array (passed by reference)
     */
    private function getTerritories(UserInterface $requester, AuthItem $authItem, array &$territories)
    {
        // we don't check the rules here, we just search for territories
        if ($requester instanceof User) {
            if ($userAssignments = $this->userAuthAssignmentRepository->findByAuthItemAndUser($authItem, $requester)) {
                // the item is directly associated with the requester
                foreach ($userAssignments as $userAssignment) {
                    /**
                     * @var UserAuthAssignment $userAssignment
                     */
                    if (!is_null($userAssignment->getTerritory())) {
                        // the authItem is associated with a territory, we add the territory to the list
                        $territories[] = $userAssignment->getTerritory()->getId();
                    } elseif (0 == count($authItem->getParents())) {
                        // the item has no parents => authorized everywhere !
                        $territories[] = 'all';

                        return;
                    }
                }
            }
            // we now search for the parents of the authItem
            foreach ($authItem->getParents() as $parent) {
                $this->getTerritories($requester, $parent, $territories);
            }
        }
    }

    /**
     * Check if a given authItem is a special one ! Special auth items need special rule to be applied.
     *
     * @param AuthItem $authItem The authItem
     *
     * @return bool Special or not
     */
    private function checkSpecialItem(AuthItem $authItem)
    {
        // we check if it's special by checking the name
        foreach (AuthItem::SPECIAL_ITEMS as $item) {
            if (false !== strpos($authItem->getName(), $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the children names of an AuthItem (recursive).
     *
     * @param AuthItem  $authItem      The auth item
     * @param int       $type          Limit to this type af Auth Item
     * @param array     $childrenNames The array of names (passed by reference)
     * @param null|bool $withId        If set to true, return also ROLE_ID
     */
    private function getChildrenNames(AuthItem $authItem, int $type, array &$childrenNames, bool $withId = false)
    {
        // we get the requester
        if (!is_null($this->user)) {
            $requester = $this->user;
        } else {
            $requester = $this->tokenStorage->getToken()->getUser();
        }
        foreach ($authItem->getItems() as $child) {
            if ($child->getType() == $type) {
                // maybe we will need some rule checking, we initialize the control value
                $rulesChecked = true;
                // for some special items, we also need to check the rule (eg. "manage" action which need the corresponding module to be enabled)
                if (AuthItem::TYPE_ITEM == $type && $this->checkSpecialItem($child)) {
                    // check the associated rule
                    $rulesChecked = $this->checkRule($requester, $child, $this->modules);
                }
                if ($rulesChecked) {
                    if ($withId) {
                        $childrenNames[] = [
                            'id' => $child,
                            'name' => $child->getName(),
                        ];
                    } else {
                        $childrenNames[] = $child->getName();
                    }
                }
            }
            $this->getChildrenNames($child, $type, $childrenNames, $withId);
        }
    }

    /**
     * Check if the role can create others roles.
     *
     * @param array $authItem     One of the roles of the current user
     * @param array $rolesGranted Array who contains all the roles current user can create
     *
     * @return array $rolesGranted       Return the array of roles for recursive goal
     */
    private function checkRolesGrantedForRole(array $authItem, array $rolesGranted)
    {
        // Array where we associate the granted roles for the roles who can cretae user
        $rolesGrantedForCreation = [
            AuthItem::ROLE_SUPER_ADMIN => [
                AuthItem::ROLE_SUPER_ADMIN,
                AuthItem::ROLE_ADMIN,
                AuthItem::ROLE_USER_REGISTERED_FULL,
                AuthItem::ROLE_USER_REGISTERED_MINIMAL,
                AuthItem::ROLE_MASS_MATCH,
                AuthItem::ROLE_COMMUNITY_MANAGER,
                AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC,
                AuthItem::ROLE_COMMUNITY_MANAGER_PRIVATE,
                AuthItem::ROLE_SOLIDARY_MANAGER,
                AuthItem::ROLE_SOLIDARY_VOLUNTEER,
                AuthItem::ROLE_SOLIDARY_BENEFICIARY,
                AuthItem::ROLE_COMMUNICATION_MANAGER,
                AuthItem::ROLE_SOLIDARY_VOLUNTEER_CANDIDATE,
                AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE,
            ],
            AuthItem::ROLE_ADMIN => [
                AuthItem::ROLE_ADMIN,
                AuthItem::ROLE_USER_REGISTERED_FULL,
                AuthItem::ROLE_USER_REGISTERED_MINIMAL,
                AuthItem::ROLE_COMMUNITY_MANAGER,
                AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC,
                AuthItem::ROLE_COMMUNITY_MANAGER_PRIVATE,
                AuthItem::ROLE_SOLIDARY_MANAGER,
                AuthItem::ROLE_SOLIDARY_VOLUNTEER,
                AuthItem::ROLE_SOLIDARY_BENEFICIARY,
                AuthItem::ROLE_COMMUNICATION_MANAGER,
                AuthItem::ROLE_SOLIDARY_VOLUNTEER_CANDIDATE,
                AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE,
            ],
            AuthItem::ROLE_SOLIDARY_MANAGER => [
                AuthItem::ROLE_USER_REGISTERED_FULL,
                AuthItem::ROLE_USER_REGISTERED_MINIMAL,
                AuthItem::ROLE_SOLIDARY_VOLUNTEER,
                AuthItem::ROLE_SOLIDARY_BENEFICIARY,
                AuthItem::ROLE_SOLIDARY_VOLUNTEER_CANDIDATE,
                AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE,
            ],
        ];
        // If the role is in our array of Roles -> granted roles, we add the roles user can create in the result array
        if (array_key_exists($authItem['id']->getId(), $rolesGrantedForCreation)) {
            $rolesGranted = array_unique(array_merge($rolesGrantedForCreation[$authItem['id']->getId()], $rolesGranted));
        }

        return $rolesGranted;
    }
}
