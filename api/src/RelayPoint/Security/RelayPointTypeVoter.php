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

namespace App\RelayPoint\Security;

use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\RelayPoint\Entity\RelayPointType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class RelayPointTypeVoter extends Voter
{
    const CREATE = 'relayPointType_create';
    const DELETE = 'relayPointType_delete';

    private $security;
    private $permissionManager;

    public function __construct(Security $security, PermissionManager $permissionManager)
    {
        $this->security = $security;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CREATE,
            self::DELETE,
            ])) {
            return false;
        }
        // only vote on RelayPoint objects inside this voter
        if (!$subject instanceof RelayPointType) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();
        
        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($requester);
            case self::DELETE:
                return $this->canDelete($requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('relay_point_type_create', $requester);
    }

    private function canDelete(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('relay_point_type_delete', $requester);
    }
}
