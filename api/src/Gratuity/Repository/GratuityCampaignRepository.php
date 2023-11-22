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

namespace App\Gratuity\Repository;

use App\Gratuity\Entity\GratuityCampaign;
use App\Gratuity\Entity\GratuityNotification;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GratuityCampaignRepository
{
    /**
     * @var EntityRepository
     */
    private $_repository;

    private $_entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->_entityManager = $entityManager;
        $this->_repository = $entityManager->getRepository(GratuityCampaign::class);
    }

    public function find(int $id): ?GratuityCampaign
    {
        return $this->_repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->_repository->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->_repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?GratuityCampaign
    {
        return $this->_repository->findOneBy($criteria);
    }

    public function findPendingForUser(User $user): array
    {
        $gratuityNotificationRepository = $this->_entityManager->getRepository(GratuityNotification::class);
        $gratuityNotificationsAlreadySeen = $gratuityNotificationRepository->createQueryBuilder('gn')
            ->select('distinct(gc.id)')
            ->join('gn.gratuityCampaign', 'gc')
            ->where('gn.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
        $mergedGratuityNotificationsAlreadySeen = [];
        if (count($gratuityNotificationsAlreadySeen) > 0) {
            $mergedGratuityNotificationsAlreadySeen = call_user_func_array('array_merge', $gratuityNotificationsAlreadySeen);
        }

        $query = $this->_repository->createQueryBuilder('gc');
        if (count($mergedGratuityNotificationsAlreadySeen) > 0) {
            $query->where($query->expr()->notIn('gc.id', $mergedGratuityNotificationsAlreadySeen));
        }

        return $query->getQuery()->getResult();
    }
}
