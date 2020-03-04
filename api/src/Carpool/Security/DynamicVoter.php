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

namespace App\Carpool\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Right\Service\PermissionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class DynamicVoter extends Voter
{
    const DYNAMIC_READ = 'dynamic_read';
    const DYNAMIC_CREATE = 'dynamic_create';
    const DYNAMIC_UPDATE = 'dynamic_update';
    const DYNAMIC_ASK_READ = 'dynamic_ask_read';
    const DYNAMIC_ASK_CREATE = 'dynamic_ask_create';
    const DYNAMIC_ASK_UPDATE = 'dynamic_ask_update';
    
    private $security;
    private $request;
    private $permissionManager;

    public function __construct(RequestStack $requestStack, Security $security, PermissionManager $permissionManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::DYNAMIC_READ,
            self::DYNAMIC_CREATE,
            self::DYNAMIC_UPDATE,
            self::DYNAMIC_ASK_READ,
            self::DYNAMIC_ASK_CREATE,
            self::DYNAMIC_ASK_UPDATE
            ])) {
            return false;
        }

        // Dynamic is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::DYNAMIC_CREATE:
                return $this->canCreatedynamic($requester);
            case self::DYNAMIC_READ:
            case self::DYNAMIC_UPDATE:
            case self::DYNAMIC_ASK_READ:
            case self::DYNAMIC_ASK_CREATE:
            case self::DYNAMIC_ASK_UPDATE:
                return true;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateDynamic(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('ad_create', $requester);
    }
}
