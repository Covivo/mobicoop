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
use App\Gratuity\Repository\GratuityCampaignRepository;
use App\Gratuity\Resource\GratuityCampaign;
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

    private $_user;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        TerritoryRepository $territoryRepository,
        GratuityCampaignRepository $gratuityCampaignRepository
    ) {
        $this->_entityManager = $entityManager;
        $this->_territoryRepository = $territoryRepository;
        $this->_gratuityCampaignRepository = $gratuityCampaignRepository;
        $this->_user = $security->getToken()->getUser();
    }

    public function createGratuityCampaign(GratuityCampaign $gratuityCampaign): ?GratuityCampaign
    {
        $entity = $this->_buildEntityGratuityCampaign($gratuityCampaign);
        $entity->setUser($this->_user);
        $entity->setStatus(EntityGratuityCampaign::STATUS_ACTIVE);

        $this->_entityManager->persist($entity);
        $this->_entityManager->flush();

        return $this->_buildGratuityCampaignFromEntity($entity);
    }

    public function getGratuityCampaign(int $gratuityCampaignId): ?GratuityCampaign
    {
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
        $campaigns = [];
        if ($entities = $this->_gratuityCampaignRepository->findAll()) {
            foreach ($entities as $entity) {
                $campaigns[] = $this->_buildGratuityCampaignFromEntity($entity);
            }
        }

        return $campaigns;
    }

    private function _buildEntityGratuityCampaign(GratuityCampaign $gratuityCampaign): EntityGratuityCampaign
    {
        $entity = new EntityGratuityCampaign();
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
