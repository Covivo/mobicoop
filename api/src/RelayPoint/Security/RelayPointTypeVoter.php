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

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\RelayPoint\Entity\RelayPointType;

class RelayPointTypeVoter extends Voter
{
    const RELAY_POINT_TYPE_CREATE = 'relay_point_type_create';
    const RELAY_POINT_TYPE_READ = 'relay_point_type_read';
    const RELAY_POINT_TYPE_UPDATE = 'relay_point_type_update';
    const RELAY_POINT_TYPE_DELETE = 'relay_point_type_delete';
    const RELAY_POINT_TYPE_LIST = 'relay_point_type_list';
    
    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::RELAY_POINT_TYPE_CREATE,
            self::RELAY_POINT_TYPE_READ,
            self::RELAY_POINT_TYPE_UPDATE,
            self::RELAY_POINT_TYPE_DELETE,
            self::RELAY_POINT_TYPE_LIST
            ])) {
            return false;
        }

        // only vote on RelayPoint objects inside this voter
        if (!in_array($attribute, [
            self::RELAY_POINT_TYPE_CREATE,
            self::RELAY_POINT_TYPE_READ,
            self::RELAY_POINT_TYPE_UPDATE,
            self::RELAY_POINT_TYPE_DELETE,
            self::RELAY_POINT_TYPE_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof RelayPointType)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::RELAY_POINT_TYPE_CREATE:
                return $this->canCreateRelayPointType();
            case self::RELAY_POINT_TYPE_READ:
                return $this->canReadRelayPointType($subject);
            case self::RELAY_POINT_TYPE_UPDATE:
                return $this->canUpdateRelayPointType($subject);
            case self::RELAY_POINT_TYPE_DELETE:
                return $this->canDeleteRelayPointType($subject);
            case self::RELAY_POINT_TYPE_LIST:
                return $this->canListRelayPointType();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateRelayPointType()
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_TYPE_CREATE);
    }

    private function canReadRelayPointType(RelayPointType $relayPointType)
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_TYPE_READ, ['relayPointType'=>$relayPointType]);
    }

    private function canUpdateRelayPointType(RelayPointType $relayPointType)
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_TYPE_UPDATE, ['relayPointType'=>$relayPointType]);
    }

    private function canDeleteRelayPointType(RelayPointType $relayPointType)
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_TYPE_DELETE, ['relayPointType'=>$relayPointType]);
    }

    private function canListRelayPointType()
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_TYPE_LIST);
    }
}
