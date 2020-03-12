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
 **************************/

namespace App\Auth\Service;

use App\App\Entity\App;
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\Permission;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Interfaces\AuthRuleInterface;
use App\User\Entity\User;
use App\Auth\Exception\AuthItemNotFoundException;
use App\Auth\Repository\AuthItemRepository;
use App\Auth\Repository\UserAuthAssignmentRepository;
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

    /**
     * Constructor.
     */
    public function __construct(
        AuthItemRepository $authItemRepository,
        UserAuthAssignmentRepository $userAuthAssignmentRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authItemRepository = $authItemRepository;
        $this->userAuthAssignmentRepository = $userAuthAssignmentRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Check if a requester has an authorization on an item.
     * The requester is retrieved from the connection token.
     *
     * @param string    $itemName   The name of the item to check
     * @param array     $params     The params associated with the item
     * @return bool
     */
    public function isAuthorized(string $itemName, array $params = [])
    {
        if (is_null($this->tokenStorage->getToken())) {
            // anonymous connection => any right should be denied, as allowed resources won't be checked for permissions
            return false;
        }
        if (!$item = $this->authItemRepository->findByName($itemName)) {
            throw new AuthItemNotFoundException('Auth item ' . $itemName . ' not found');
        }
       
        // we get the requester
        $requester = $this->tokenStorage->getToken()->getUser();
       
        // check if the item is authorized for the requester
        return $this->isAssigned($requester, $item, $params);
    }

    /**
     * Check if a requester is assigned an auth item (recursive).
     *
     * @param UserInterface $requester  The requester
     * @param AuthItem $authItem        The auth item
     * @param array $params             The params needed to check the authorization (will be passed to the rule if it exists)
     * @return boolean  True if the requester is assigned the item, false either
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
                } else {
                    // the item is not assigned, we check its parents
                    foreach ($authItem->getParents() as $parent) {
                        if ($this->isAssigned($requester, $parent, $params)) {
                            return true;
                        }
                    }
                }
            } elseif ($requester instanceof App) {
                if (in_array($authItem, $requester->getAuthItems())) {
                    // the item is found
                    return true;
                } else {
                    // the item is not assigned, we check its parents
                    foreach ($authItem->getParents() as $parent) {
                        if ($this->isAssigned($requester, $parent, $params)) {
                            return true;
                        }
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
     * @param UserInterface $requester  The requester
     * @param AuthItem $authItem        The auth item
     * @param array $params             The params needed to check the authorization
     * @return boolean  True if there's no rule or if the rule is validated, false either
     */
    private function checkRule(UserInterface $requester, AuthItem $authItem, array $params)
    {
        if (is_null($authItem->getAuthRule())) {
            // no rule associated, we're good !
            return true;
        }
        // at this point a rule is associated, we need to execute it
        $authRuleName = "\\App\\Auth\Rule\\" . $authItem->getAuthRule()->getName();
        /**
         * @var AuthRuleInterface $authRule
         */
        $authRule = new $authRuleName;
        return $authRule->execute($requester, $authItem, $params);
    }

    /**
     * Get the allowed territories to the current requester for an auth item
     *
     * @param string    $itemName   The name of the item to check
     * @return array The array of territories where the requester is authorized (empty array if the requester is authorized on any territory)
     */
    public function getTerritoriesForItem(string $itemName)
    {
        if (!$item = $this->authItemRepository->findByName($itemName)) {
            throw new AuthItemNotFoundException('Auth item ' . $itemName . ' not found');
        }

        // we get the requester
        $requester = $this->tokenStorage->getToken()->getUser();

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
     * Create an array of allowed territories for an item (recursive).
     * For now just for Users, not Apps.
     *
     * @param UserInterface $requester      The requester
     * @param AuthItem      $authItem       The authItem to check
     * @param array         $territories    The territories array (passed by reference)
     * @return void
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
                    } elseif (count($authItem->getParents()) == 0) {
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
     * Check if a requester has an authorization on an item, and returns a Permission object.
     * The requester is retrieved from the connection token.
     *
     * @param string    $itemName   The name of the item to check
     * @param array     $params     The params associated with the item
     * @return Permission           The permission
     */
    public function getPermissionForAuthItem(string $itemName, array $params = [])
    {
        $permission = new Permission(1);
        $permission->setGranted($this->isAuthorized($itemName, $params));
        return $permission;
    }

    /**
     * Return the assigned AuthItem of the current user
     *
     * @return array The auth items
     */
    public function getAuthItems()
    {
        $authItems = [];

        // we get the requester
        $requester = $this->tokenStorage->getToken()->getUser();

        if ($userAssignments = $this->userAuthAssignmentRepository->findByUser($requester)) {
            foreach ($userAssignments as $userAssignment) {
                if ($userAssignment->getAuthItem()->getType() == AuthItem::TYPE_ITEM) {
                    $authItems[] = $userAssignment->getAuthItem()->getName();
                }
                $this->getChildrenNames($userAssignment->getAuthItem(), $authItems);
            }
        }
        return array_unique($authItems);
    }

    /**
     * Get the children names of an AuthItem (recursive)
     *
     * @param AuthItem  $authItem       The auth item
     * @param array     $childrenNames  The array of names (passed by reference)
     * @return void
     */
    private function getChildrenNames(AuthItem $authItem, array &$childrenNames)
    {
        foreach ($authItem->getItems() as $child) {
            if ($child->getType() == AuthItem::TYPE_ITEM) {
                $childrenNames[] = $child->getName();
            }
            $this->getChildrenNames($child, $childrenNames);
        }
    }
}
