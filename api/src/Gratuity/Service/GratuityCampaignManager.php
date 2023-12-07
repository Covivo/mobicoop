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

use App\Geography\Repository\TerritoryRepository;
use App\Gratuity\Entity\GratuityCampaign as EntityGratuityCampaign;
use App\Gratuity\Entity\GratuityNotification;
use App\Gratuity\Repository\GratuityCampaignNotificationRepository;
use App\Gratuity\Repository\GratuityCampaignRepository;
use App\Gratuity\Resource\GratuityCampaign;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GratuityCampaignManager
{
    private $_entityManager;
    private $_territoryRepository;
    private $_gratuityCampaignRepository;
    private $_gratuityCampaignNotificationRepository;

    /**
     * @var User
     */
    private $_user;
    private $_gratuityCampaignActive;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        TerritoryRepository $territoryRepository,
        GratuityCampaignRepository $gratuityCampaignRepository,
        GratuityCampaignNotificationRepository $gratuityCampaignNotificationRepository,
        bool $gratuityCampaignActive
    ) {
        $this->_entityManager = $entityManager;
        $this->_territoryRepository = $territoryRepository;
        $this->_gratuityCampaignRepository = $gratuityCampaignRepository;
        $this->_gratuityCampaignNotificationRepository = $gratuityCampaignNotificationRepository;
        $this->_user = $security->getToken()->getUser();
        $this->_gratuityCampaignActive = $gratuityCampaignActive;
    }

    public function createGratuityCampaign(GratuityCampaign $gratuityCampaign): ?GratuityCampaign
    {
        if (!$this->_isGratuityActive()) {
            return null;
        }

        $entity = $this->_buildEntityGratuityCampaign($gratuityCampaign);
        $entity->setUser($this->_user);
        $entity->setStatus(EntityGratuityCampaign::STATUS_ACTIVE);

        $this->_entityManager->persist($entity);
        $this->_entityManager->flush();

        return $this->_buildGratuityCampaignFromEntity($entity);
    }

    public function getGratuityCampaign(int $gratuityCampaignId): ?GratuityCampaign
    {
        if (!$this->_isGratuityActive()) {
            return null;
        }

        if ($entity = $this->_gratuityCampaignRepository->find($gratuityCampaignId)) {
            return $this->_buildGratuityCampaignFromEntity($entity);
        }

        return null;
    }

    /**
     * @return GratuityCampaign[]
     */
    public function getGratuityCampaigns(): ?array
    {
        if (!$this->_isGratuityActive()) {
            return null;
        }

        $campaigns = [];
        if ($entities = $this->_gratuityCampaignRepository->findAll()) {
            foreach ($entities as $entity) {
                $campaigns[] = $this->_buildGratuityCampaignFromEntity($entity);
            }
        }

        return $campaigns;
    }

    public function tagAsNotified(int $campaignId): ?GratuityCampaign
    {
        if (!$this->_isGratuityActive()) {
            return null;
        }

        if ($entity = $this->_gratuityCampaignRepository->find($campaignId)) {
            $notifications = $this->_getExistingNotification($entity);
            if (count($notifications) > 0) {
                $this->_tagExistingNotificationAsSeen($notifications[0]);
            } else {
                $this->_tagNewExistingNotificationAsSeen($entity);
            }

            $gratuityCampaign = $this->_buildGratuityCampaignFromEntity($entity);
            $gratuityCampaign->setNotifiedForThisUser(true);

            return $gratuityCampaign;
        }

        return null;
    }

    private function _isGratuityActive()
    {
        return $this->_user->hasGratuity() && $this->_gratuityCampaignActive;
    }

    private function _tagNewExistingNotificationAsSeen(EntityGratuityCampaign $gratuityCampaign)
    {
        $notification = new GratuityNotification();
        $notification->setUser($this->_user);
        $notification->setGratuityCampaign($gratuityCampaign);
        $notification->setNotifiedDate(new \DateTime('now'));
        $gratuityCampaign->addGratuityNotification($notification);

        $this->_entityManager->persist($gratuityCampaign);
        $this->_entityManager->flush();
    }

    private function _tagExistingNotificationAsSeen(GratuityNotification $notification)
    {
        $notification->setNotifiedDate(new \DateTime('now'));
        $this->_entityManager->persist($notification);
        $this->_entityManager->flush();
    }

    private function _getExistingNotification(EntityGratuityCampaign $gratuityCampaign): array
    {
        return $this->_gratuityCampaignNotificationRepository->findBy(['user' => $this->_user, 'gratuityCampaign' => $gratuityCampaign]);
    }

    private function _buildEntityGratuityCampaign(GratuityCampaign $gratuityCampaign): EntityGratuityCampaign
    {
        $entity = new EntityGratuityCampaign();
        $entity->setId($gratuityCampaign->getId());
        $entity->setName($gratuityCampaign->getName());
        $entity->setUser($gratuityCampaign->getUser());
        $entity->setTemplate($gratuityCampaign->getTemplate());
        $entity->setStatus($gratuityCampaign->getStatus());
        $entity->setStartDate($gratuityCampaign->getStartDate());
        $entity->setEndDate($gratuityCampaign->getEndDate());

        foreach ($gratuityCampaign->getTerritories() as $territoryId) {
            if (is_numeric($territoryId) && $territory = $this->_territoryRepository->find($territoryId)) {
                $entity->addTerritory($territory);
            }
        }

        return $entity;
    }

    private function _buildGratuityCampaignFromEntity(EntityGratuityCampaign $entity): GratuityCampaign
    {
        $gratuityCampaign = new GratuityCampaign();
        $gratuityCampaign->setId($entity->getId());
        $gratuityCampaign->setName($entity->getName());
        $gratuityCampaign->setUser($entity->getUser());
        $gratuityCampaign->setTemplate($entity->getTemplate());
        $gratuityCampaign->setStatus($entity->getStatus());
        $gratuityCampaign->setStartDate($entity->getStartDate());
        $gratuityCampaign->setEndDate($entity->getEndDate());
        $gratuityCampaign->setCreatedDate($entity->getCreatedDate());
        $gratuityCampaign->setUpdatedDate($entity->getUpdatedDate());

        $gratuityCampaign->setTerritories([]);
        foreach ($entity->getTerritories() as $territory) {
            $gratuityCampaign->pushTerritory($territory->getId());
        }

        return $gratuityCampaign;
    }
}
