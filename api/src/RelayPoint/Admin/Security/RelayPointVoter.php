<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\RelayPoint\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class RelayPointVoter extends Voter
{
    public const ADMIN_RELAY_POINT_CREATE = 'admin_relay_point_create';
    public const ADMIN_RELAY_POINT_READ = 'admin_relay_point_read';
    public const ADMIN_RELAY_POINT_UPDATE = 'admin_relay_point_update';
    public const ADMIN_RELAY_POINT_DELETE = 'admin_relay_point_delete';
    public const ADMIN_RELAY_POINT_LIST = 'admin_relay_point_list';
    public const RELAY_POINT_CREATE = 'relay_point_create';
    public const RELAY_POINT_READ = 'relay_point_read';
    public const RELAY_POINT_UPDATE = 'relay_point_update';
    public const RELAY_POINT_DELETE = 'relay_point_delete';
    public const RELAY_POINT_LIST = 'relay_point_list';

    private $authManager;
    private $request;
    private $relayPointRepository;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, RelayPointRepository $relayPointRepository)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->relayPointRepository = $relayPointRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_RELAY_POINT_CREATE,
            self::ADMIN_RELAY_POINT_READ,
            self::ADMIN_RELAY_POINT_UPDATE,
            self::ADMIN_RELAY_POINT_DELETE,
            self::ADMIN_RELAY_POINT_LIST
            ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_RELAY_POINT_CREATE,
            self::ADMIN_RELAY_POINT_READ,
            self::ADMIN_RELAY_POINT_UPDATE,
            self::ADMIN_RELAY_POINT_DELETE,
            self::ADMIN_RELAY_POINT_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof RelayPoint)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ADMIN_RELAY_POINT_CREATE:
                return $this->canCreateRelayPoint();
            case self::ADMIN_RELAY_POINT_READ:
                // this voter is used for direct relay point read, we have to check the type of subject
                if ($subject instanceof RelayPoint) {
                    return $this->canReadRelayPoint($subject);
                }
                if ($relayPoint = $this->relayPointRepository->find($this->request->get('id'))) {
                    return $this->canReadRelayPoint($relayPoint);
                }
                return false;
            case self::ADMIN_RELAY_POINT_UPDATE:
                return $this->canUpdateRelayPoint($subject);
            case self::ADMIN_RELAY_POINT_DELETE:
                return $this->canDeleteRelayPoint($subject);
            case self::ADMIN_RELAY_POINT_LIST:
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
