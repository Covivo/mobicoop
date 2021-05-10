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
use App\RelayPoint\Entity\RelayPointType;
use App\RelayPoint\Repository\RelayPointTypeRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class RelayPointTypeVoter extends Voter
{
    const ADMIN_RELAY_POINT_TYPE_CREATE = 'admin_relay_point_type_create';
    const ADMIN_RELAY_POINT_TYPE_READ = 'admin_relay_point_type_read';
    const ADMIN_RELAY_POINT_TYPE_UPDATE = 'admin_relay_point_type_update';
    const ADMIN_RELAY_POINT_TYPE_DELETE = 'admin_relay_point_type_delete';
    const ADMIN_RELAY_POINT_TYPE_LIST = 'admin_relay_point_type_list';
    const RELAY_POINT_TYPE_CREATE = 'relay_point_type_create';
    const RELAY_POINT_TYPE_READ = 'relay_point_type_read';
    const RELAY_POINT_TYPE_UPDATE = 'relay_point_type_update';
    const RELAY_POINT_TYPE_DELETE = 'relay_point_type_delete';
    const RELAY_POINT_TYPE_LIST = 'relay_point_type_list';

    private $authManager;
    private $request;
    private $relayPointTypeRepository;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, RelayPointTypeRepository $relayPointTypeTypeRepository)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->relayPointTypeRepository = $relayPointTypeTypeRepository;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_RELAY_POINT_TYPE_CREATE,
            self::ADMIN_RELAY_POINT_TYPE_READ,
            self::ADMIN_RELAY_POINT_TYPE_UPDATE,
            self::ADMIN_RELAY_POINT_TYPE_DELETE,
            self::ADMIN_RELAY_POINT_TYPE_LIST
            ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_RELAY_POINT_TYPE_CREATE,
            self::ADMIN_RELAY_POINT_TYPE_READ,
            self::ADMIN_RELAY_POINT_TYPE_UPDATE,
            self::ADMIN_RELAY_POINT_TYPE_DELETE,
            self::ADMIN_RELAY_POINT_TYPE_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof RelayPointType)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_RELAY_POINT_TYPE_CREATE:
                return $this->canCreateRelayPointType();
            case self::ADMIN_RELAY_POINT_TYPE_READ:
                // this voter is used for direct relay point type read, we have to check the type of subject
                if ($subject instanceof RelayPointType) {
                    return $this->canReadRelayPointType($subject);
                }
                if ($relayPointType = $this->relayPointTypeRepository->find($this->request->get('id'))) {
                    return $this->canReadRelayPointType($relayPointType);
                }
                return false;
            case self::ADMIN_RELAY_POINT_TYPE_UPDATE:
                return $this->canUpdateRelayPointType($subject);
            case self::ADMIN_RELAY_POINT_TYPE_DELETE:
                return $this->canDeleteRelayPointType($subject);
            case self::ADMIN_RELAY_POINT_TYPE_LIST:
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
