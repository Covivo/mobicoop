<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\RelayPoint\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\RelayPoint\Resource\RelayPointMap;
use App\RelayPoint\Service\RelayPointMapManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RelayPointMapVoter extends Voter
{
    public const RELAYPOINTMAP_LIST = 'relay_point_map_list';

    private $request;
    private $relayPointMapManager;

    public function __construct(AuthManager $authManager, RequestStack $requestStack, RelayPointMapManager $relayPointMapManager)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->relayPointMapManager = $relayPointMapManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::RELAYPOINTMAP_LIST,
        ])) {
            return false;
        }

        // only vote on RelayPointMap objects inside this voter
        if (!in_array($attribute, [
            self::RELAYPOINTMAP_LIST,
        ]) && !($subject instanceof Paginator) && !($subject instanceof RelayPointmAP)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::RELAYPOINTMAP_LIST:
                return $this->canListRelayPointMap();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canListRelayPointMap()
    {
        return $this->authManager->isAuthorized(self::RELAYPOINTMAP_LIST);
    }
}
