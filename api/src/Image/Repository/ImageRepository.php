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
 **************************/

namespace App\Image\Repository;

use App\Community\Entity\Community;
use App\Event\Entity\Event;
use App\Image\Entity\Image;
use App\MassCommunication\Entity\Campaign;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Image::class);
    }

    public function find(int $id): ?Image
    {
        return $this->repository->find($id);
    }
    
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }
    
    /**
     * Find the next image position for a given related entity (owner)
     * @param $owner
     */
    public function findNextPosition($owner)
    {
        $query = $this->repository->createQueryBuilder('i');
        $query->select('MAX(i.position) AS maxPos');
        switch (get_class($owner)) {
            case Event::class:
                $query->andWhere('i.event = :event')
                ->setParameter('event', $owner);
                break;
            case User::class:
                $query->andWhere('i.user = :user')
                ->setParameter('user', $owner);
                break;
            case Community::class:
                $query->andWhere('i.community = :community')
                ->setParameter('community', $owner);
                break;
            case Campaign::class:
                $query->andWhere('i.campaign = :campaign')
                    ->setParameter('campaign', $owner);
                break;
            default:
                break;
        }
        if ($result = $query->getQuery()->getOneOrNullResult()) {
            return 1+$result['maxPos'];
        }
        return 1;
    }
}
