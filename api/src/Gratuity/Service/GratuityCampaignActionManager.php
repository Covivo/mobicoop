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

namespace App\Gratuity\Service;

use App\Geography\Entity\Address;
use App\Geography\Repository\TerritoryRepository;
use App\Gratuity\Entity\GratuityCampaign;
use App\Gratuity\Entity\GratuityNotification;
use App\Gratuity\Repository\GratuityCampaignRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GratuityCampaignActionManager
{
    private $_entityManager;
    private $_territoryRepository;
    private $_gratuityCampaignRepository;
    private $_user;

    public function __construct(
        EntityManagerInterface $entityManager,
        TerritoryRepository $territoryRepository,
        GratuityCampaignRepository $gratuityCampaignRepository
    ) {
        $this->_territoryRepository = $territoryRepository;
        $this->_gratuityCampaignRepository = $gratuityCampaignRepository;
        $this->_entityManager = $entityManager;
    }

    public function handleAction(User $user)
    {
        $this->_user = $user;
        $territories = $this->_findTerritoriesIdOfAddress($this->_user->getHomeAddress());
        $campaigns = $this->_gratuityCampaignRepository->findPendingForUserWithTerritories($this->_user, $territories);
        if (count($campaigns) > 0) {
            $this->_createGratuityNotifications($campaigns);
        }
    }

    private function _findTerritoriesIdOfAddress(Address $homeAddress): ?array
    {
        $territories = $this->_territoryRepository->findPointTerritories($homeAddress->getLatitude(), $homeAddress->getLongitude());
        $ids = [];
        foreach ($territories as $territory) {
            $ids[] = $territory['id'];
        }

        return $ids;
    }

    private function _createGratuityNotification(GratuityCampaign $gratuityCampaign)
    {
        $gratuityCampaignNotification = new GratuityNotification();
        $gratuityCampaignNotification->setUser($this->_user);
        $gratuityCampaignNotification->setGratuityCampaign($gratuityCampaign);
        $this->_entityManager->persist($gratuityCampaignNotification);
    }

    private function _createGratuityNotifications(array $campaigns)
    {
        foreach ($campaigns as $campaign) {
            $this->_createGratuityNotification($campaign);
        }
        $this->_entityManager->flush();
    }
}
