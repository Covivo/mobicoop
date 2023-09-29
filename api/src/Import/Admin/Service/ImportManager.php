<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Import\Admin\Service;

use App\Community\Admin\Service\CommunityManager;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\RelayPoint\Admin\Service\RelayPointManager;
use App\RelayPoint\Entity\RelayPoint;
use App\User\Admin\Service\UserManager;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ImportManager
{
    private $_userManager;
    private $_relayPointManager;
    private $_communityManager;
    private $_entityManager;

    public function __construct(UserManager $userManager, RelayPointManager $relayPointManager, CommunityManager $communityManager, EntityManagerInterface $entityManager)
    {
        $this->_userManager = $userManager;
        $this->_relayPointManager = $relayPointManager;
        $this->_communityManager = $communityManager;
        $this->_entityManager = $entityManager;
    }

    public function addUser(User $user): User
    {
        return $this->_userManager->addUser($user);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->_userManager->getUserByEmail($email);
    }

    public function addRelayPoint(RelayPoint $relayPoint): ?RelayPoint
    {
        return $this->_relayPointManager->addRelayPoint($relayPoint);
    }

    public function getByLatLon(float $lat, float $lon)
    {
        return $this->_relayPointManager->getByLatLon(round($lat, 6), round($lon, 6));
    }

    public function getByLatLonOrExternalId(float $lat, float $lon, string $externalId)
    {
        return $this->_relayPointManager->getByLatLonOrExternalId(round($lat, 6), round($lon, 6), $externalId);
    }

    public function getRelayPointTypeById(int $id)
    {
        return $this->_relayPointManager->getRelayPointTypeById($id);
    }

    public function getCommunity(int $id): ?Community
    {
        return $this->_communityManager->getCommunity($id);
    }

    public function signUpUserInACommunity(Community $community, User $user)
    {
        $communityUser = new CommunityUser();
        $communityUser->setCommunity($community);
        $communityUser->setUser($user);
        $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED_AS_MEMBER);
        $communityUser->setAcceptedDate(new \DateTime('now'));

        $this->_entityManager->persist($communityUser);
        $this->_entityManager->flush();
    }
}
