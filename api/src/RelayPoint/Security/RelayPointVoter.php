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
use App\RelayPoint\Entity\RelayPoint;

class RelayPointVoter extends Voter
{
    public const RELAY_POINT_CREATE = 'relay_point_create';
    public const RELAY_POINT_READ = 'relay_point_read';
    public const RELAY_POINT_UPDATE = 'relay_point_update';
    public const RELAY_POINT_DELETE = 'relay_point_delete';
    public const RELAY_POINT_LIST = 'relay_point_list';

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::RELAY_POINT_CREATE,
            self::RELAY_POINT_READ,
            self::RELAY_POINT_UPDATE,
            self::RELAY_POINT_DELETE,
            self::RELAY_POINT_LIST
            ])) {
            return false;
        }

        // only vote on RelayPoint objects inside this voter
        if (!in_array($attribute, [
            self::RELAY_POINT_CREATE,
            self::RELAY_POINT_READ,
            self::RELAY_POINT_UPDATE,
            self::RELAY_POINT_DELETE,
            self::RELAY_POINT_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof RelayPoint)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::RELAY_POINT_CREATE:
                return $this->canCreateRelayPoint();
            case self::RELAY_POINT_READ:
                return $this->canReadRelayPoint($subject);
            case self::RELAY_POINT_UPDATE:
                return $this->canUpdateRelayPoint($subject);
            case self::RELAY_POINT_DELETE:
                return $this->canDeleteRelayPoint($subject);
            case self::RELAY_POINT_LIST:
                return $this->canListRelayPoint();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateRelayPoint()
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_CREATE);
    }

    private function canReadRelayPoint(RelayPoint $relayPoint)
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_READ, ['relayPoint'=>$relayPoint]);
    }

    private function canUpdateRelayPoint(RelayPoint $relayPoint)
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_UPDATE, ['relayPoint'=>$relayPoint]);
    }

    private function canDeleteRelayPoint(RelayPoint $relayPoint)
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_DELETE, ['relayPoint'=>$relayPoint]);
    }

    private function canListRelayPoint()
    {
        return $this->authManager->isAuthorized(self::RELAY_POINT_LIST);
    }
}
