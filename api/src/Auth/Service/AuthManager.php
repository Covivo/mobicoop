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
 * @author Sylvain Briat <sylvain.briat@mobioop.org>
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
     * @param array     $params     The params associated with the right
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
        
        // check if the item is authorized for the user
        return $this->isAssigned($requester, $item, $params);
    }

    private function isAssigned(UserInterface $requester, AuthItem $authItem, array $params)
    {
        // we check if the item is directly assigned to the user
        if ($requester instanceof User) {
            if ($this->userAuthAssignmentRepository->findByAuthItemAndUser($authItem, $requester)) {
                // the item is found, we check if there's a rule
                if ($this->checkRule($requester, $authItem, $params)) {
                    // no rule or rule validated
                    return true;
                }
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
                // the item is found, we check if there's a rule
                if ($this->checkRule($requester, $authItem, $params)) {
                    // no rule or rule validated
                    return true;
                }
            } else {
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

    private function checkRule(UserInterface $requester, AuthItem $authItem, array $params)
    {
        if (is_null($authItem->getAuthRule())) {
            // no rule associated, we're good !
            return true;
        }
        // at this point a rule is associated, we need to execute it
        $authRuleName = '\\App\\Auth\\Rule\\' . $authItem->getAuthRule()->getName();
        /**
         * @var AuthRuleInterface $authRule
         */
        $authRule = new $authRuleName();
        return $authRule->execute($requester, $authItem, $params);
    }
}
