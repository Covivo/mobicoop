<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Geography\Service\Point;

use App\Community\Entity\CommunityUser;
use App\Geography\Ressource\Point;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;

class RelayPointPointProvider implements PointProvider
{
    protected $relayPointRepository;
    protected $maxResults;
    protected $params;

    public function __construct(RelayPointRepository $relayPointRepository)
    {
        $this->relayPointRepository = $relayPointRepository;
        $this->setMaxResults(0);
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function search(string $search, ?User $user = null): array
    {
        return $this->relayPointsToPoints(
            $this->relayPointRepository->findByParams($search, $this->params),
            ($user instanceof User) ? $user : null
        );
    }

    private function relayPointsToPoints(array $relayPoints, ?User $user = null): array
    {
        $points = [];
        foreach ($relayPoints as $relayPoint) {
            $userExcluded = false;
            if ($relayPoint->getCommunity() && $relayPoint->isPrivate()) {
                $userExcluded = true;
                if ($user) {
                    foreach ($relayPoint->getCommunity()->getCommunityUsers() as $communityUser) {
                        if (
                            $communityUser->getUser()->getId() == $user->getId()
                            && (
                                CommunityUser::STATUS_ACCEPTED_AS_MEMBER == $communityUser->getStatus()
                                || CommunityUser::STATUS_ACCEPTED_AS_MODERATOR == $communityUser->getStatus()
                            )
                        ) {
                            $userExcluded = false;

                            break;
                        }
                    }
                }
            }
            if (!$userExcluded) {
                $points[] = $this->relayPointToPoint($relayPoint);
                if ($this->maxResults > 0 && count($points) == $this->maxResults) {
                    break;
                }
            }
        }

        return $points;
    }

    private function relayPointToPoint(RelayPoint $relayPoint): Point
    {
        $point = AddressAdapter::addressToPoint($relayPoint->getAddress());
        $point->setId((string) $relayPoint->getId());
        $point->setName($relayPoint->getName());
        $point->setType('relaypoint');
        $point->setIcon(!is_null($relayPoint->getRelayPointType()) ? $relayPoint->getRelayPointType()->getIcon() : null);

        return $point;
    }
}
