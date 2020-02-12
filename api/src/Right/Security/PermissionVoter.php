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

namespace App\Right\Security;

use App\Geography\Exception\TerritoryNotFoundException;
use App\Geography\Repository\TerritoryRepository;
use App\Right\Exception\RightException;
use App\Right\Exception\RightNotFoundException;
use App\Right\Repository\RightRepository;
use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\User\Exception\UserNotFoundException;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionVoter extends Voter
{
    const PERMISSION = 'permission';

    private $permissionManager;
    private $request;
    private $userRepository;
    private $rightRepository;
    private $territoryRepository;

    public function __construct(PermissionManager $permissionManager, RequestStack $requestStack, UserRepository $userRepository, RightRepository $rightRepository, TerritoryRepository $territoryRepository)
    {
        $this->permissionManager = $permissionManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->userRepository = $userRepository;
        $this->rightRepository = $rightRepository;
        $this->territoryRepository = $territoryRepository;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::PERMISSION
            ])) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();
        switch ($attribute) {
            case self::PERMISSION:
                return $this->canCheckPermission($requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCheckPermission(UserInterface $requester)
    {
        // we check if the user exists
        if (!$this->request->get("user")) {
            throw new RightException('User id is mandatory');
        }
        if (!$user = $this->userRepository->find($this->request->get("user"))) {
            throw new UserNotFoundException('User #' . $this->request->get("user") . ' not found');
        }
        return $this->permissionManager->canCheckPermission($requester, $user);
    }
}
