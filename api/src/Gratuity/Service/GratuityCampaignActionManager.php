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

use App\Carpool\Entity\Proposal;
use App\Geography\Entity\Address;
use App\Geography\Repository\TerritoryRepository;
use App\Gratuity\Entity\GratuityCampaign;
use App\Gratuity\Entity\GratuityNotification;
use App\Gratuity\Repository\GratuityCampaignNotificationRepository;
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
    private $_gratuityNotificationRepository;

    /**
     * @var User
     */
    private $_user;

    /**
     * @var array
     */
    private $_index_user_gratuity_campaigns_ids;

    public function __construct(
        EntityManagerInterface $entityManager,
        TerritoryRepository $territoryRepository,
        GratuityCampaignRepository $gratuityCampaignRepository,
        GratuityCampaignNotificationRepository $gratuityNotificationRepository
    ) {
        $this->_territoryRepository = $territoryRepository;
        $this->_gratuityCampaignRepository = $gratuityCampaignRepository;
        $this->_gratuityNotificationRepository = $gratuityNotificationRepository;
        $this->_entityManager = $entityManager;
    }

    public function handleHomeAddressUpdatedAction(User $user)
    {
        $this->_user = $user;
        $this->_indexUserGratuityNotifications();
        $territories = $this->_findTerritoriesIdOfAddress($this->_user->getHomeAddress());
        if (count($territories) > 0) {
            $campaigns = $this->_gratuityCampaignRepository->findPendingForUserWithTerritories($this->_user, $territories);
            if (count($campaigns) > 0) {
                $this->_createGratuityNotifications($campaigns);
            }
        }
    }

    public function handleCarpoolAdPostedAction(User $user, Proposal $proposal)
    {
        // Handle ad posted action
    }

    private function _indexUserGratuityNotifications()
    {
        $this->_index_user_gratuity_campaigns_ids = [];
        $notifications = $this->_gratuityNotificationRepository->findBy(['user' => $this->_user]);
        foreach ($notifications as $notification) {
            $this->_index_user_gratuity_campaigns_ids[] = $notification->getGratuityCampaign()->getId();
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
        if (!in_array($gratuityCampaign->getId(), $this->_index_user_gratuity_campaigns_ids)) {
            $gratuityCampaignNotification = new GratuityNotification();
            $gratuityCampaignNotification->setUser($this->_user);
            $gratuityCampaignNotification->setGratuityCampaign($gratuityCampaign);
            $this->_entityManager->persist($gratuityCampaignNotification);
            $this->_index_user_gratuity_campaigns_ids[] = $gratuityCampaign->getId();
        }
    }

    private function _createGratuityNotifications(array $campaigns)
    {
        // $this->_user_gratuity_notifications = $this->_gratuityNotificationRepository->findBy(['user' => $this->_user]);
        foreach ($campaigns as $campaign) {
            $this->_createGratuityNotification($campaign);
        }
        $this->_entityManager->flush();
    }
}
