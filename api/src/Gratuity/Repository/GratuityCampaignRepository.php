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

use App\Geography\Repository\TerritoryRepository;
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
    private $_territoryRepository;
    private $_gratuityCampaignNotificationRepository;

    public function __construct(EntityManagerInterface $entityManager, TerritoryRepository $territoryRepository, GratuityCampaignNotificationRepository $gratuityCampaignNotificationRepository)
    {
        $this->_entityManager = $entityManager;
        $this->_repository = $entityManager->getRepository(GratuityCampaign::class);
        $this->_territoryRepository = $territoryRepository;
        $this->_gratuityCampaignNotificationRepository = $gratuityCampaignNotificationRepository;
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
        $territories = $this->_getTerritoriesUser($user);
        if (0 == count($territories)) {
            return $this->_findPendingForUserWithoutTerritories($user);
        }

        return $this->findPendingForUserWithTerritories($user, $territories);
    }

    public function findPendingForUserWithTerritories(User $user, array $territories): array
    {
        $today = new \DateTime('now');

        $mergedGratuityNotificationsAlreadySeen = $this->_getAlreadySeenGratuityNotificationForUser($user);

        $query = $this->_repository->createQueryBuilder('gc')
            ->where('gc.startDate <= :today')
            ->andWhere('gc.endDate >= :today')
            ->andWhere('gc.status = :active')
        ;
        if (count($mergedGratuityNotificationsAlreadySeen) > 0) {
            $query->andWhere($query->expr()->notIn('gc.id', $mergedGratuityNotificationsAlreadySeen));
        }
        if (count($territories) > 0) {
            $query->leftJoin('gc.territories', 'gct')
                ->andWhere('gct.id is null OR '.$query->expr()->in('gct.id', $territories))
            ;
        }

        $query->setParameter('today', $today->format('Y-m-d H:i:s'));
        $query->setParameter('active', GratuityCampaign::STATUS_ACTIVE);

        return $query->getQuery()->getResult();
    }

    private function _findPendingForUserWithoutTerritories(User $user): array
    {
        $query = $this->_repository->createQueryBuilder('gc')
            ->join('gc.notifications', 'gcn')
            ->where('gcn.user = :user')
            ->andWhere('gcn.notifiedDate is null')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    private function _getTerritoriesUser(User $user)
    {
        $territories = [];

        $homeAddressTerritories = $this->_getHomeAddressTerritoriesForUser($user);

        return array_unique(array_merge($territories, $homeAddressTerritories));
    }

    private function _getHomeAddressTerritoriesForUser(User $user)
    {
        $territories = [];
        $homeAddress = $user->getHomeAddress();
        if (!is_null($homeAddress)) {
            foreach ($homeAddress->getTerritories() as $territory) {
                $territories[] = $territory->getId();
            }
        }

        return $territories;
    }

    private function _getAlreadySeenGratuityNotificationForUser(User $user)
    {
        $today = new \DateTime('now');

        $gratuityNotificationRepository = $this->_entityManager->getRepository(GratuityNotification::class);
        $gratuityNotificationsAlreadySeen = $gratuityNotificationRepository->createQueryBuilder('gn')
            ->select('distinct(gc.id)')
            ->join('gn.gratuityCampaign', 'gc')
            ->where('gn.user = :user')
            ->andWhere('gc.startDate <= :today')
            ->andWhere('gc.endDate >= :today')
            ->andWhere('gn.notifiedDate is not null')
            ->setParameter('user', $user)
            ->setParameter('today', $today->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;
        $mergedGratuityNotificationsAlreadySeen = [];
        if (count($gratuityNotificationsAlreadySeen) > 0) {
            $mergedGratuityNotificationsAlreadySeen = call_user_func_array('array_merge', $gratuityNotificationsAlreadySeen);
        }

        return $mergedGratuityNotificationsAlreadySeen;
    }
}
