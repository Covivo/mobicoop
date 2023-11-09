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
use App\Event\Entity\Event;
use App\Event\Service\EventManager;
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
    private $_eventManager;

    public function __construct(UserManager $userManager, RelayPointManager $relayPointManager, CommunityManager $communityManager, EntityManagerInterface $entityManager, EventManager $eventManager)
    {
        $this->_userManager = $userManager;
        $this->_relayPointManager = $relayPointManager;
        $this->_communityManager = $communityManager;
        $this->_entityManager = $entityManager;
        $this->_eventManager = $eventManager;
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

    public function updateRelayPoint(RelayPoint $relayPoint): ?RelayPoint
    {
        $this->_entityManager->persist($relayPoint);
        $this->_entityManager->flush();

        return $relayPoint;
    }

    public function getRelayPointById(int $id)
    {
        return $this->_relayPointManager->getById($id);
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

    public function addEvent(Event $event): ?Event
    {
        return $this->_eventManager->createEvent($event);
    }

    public function updateEvent(Event $event): ?Event
    {
        $this->_entityManager->persist($event);
        $this->_entityManager->flush();

        return $event;
    }

    public function deleteEvent(Event $event)
    {
        $this->_entityManager->remove($event);
        $this->_entityManager->flush();
    }

    public function getEventsByCommunity(int $communityId)
    {
        return $this->_eventManager->getEventsByCommunity($communityId);
    }

    public function getEventByExternalId(int $externalId)
    {
        return $this->_eventManager->getEventByExternalId($externalId);
    }

    public function getAllEvents()
    {
        return $this->_eventManager->getEventsWithAnExternalId();
    }
}
