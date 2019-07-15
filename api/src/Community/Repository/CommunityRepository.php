<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Community\Repository;

use App\Community\Entity\Community;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\User\Entity\User;

class CommunityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Community::class);
    }
    
    /**
     * Find community by id.
     *
     * @param integer $id
     * @return Community|null
     */
    public function find(int $id): ?Community
    {
        return $this->repository->find($id);
    }

    /**
     * Find communities for the given user
     *
     * @param User $user
     * @param boolean|null $proposalsHidden
     * @param boolean|null $membersHidden
     * @param integer|null $memberStatus
     * @return void
     */
    public function findByUser(User $user, ?bool $proposalsHidden=null, ?bool $membersHidden=null, ?int $memberStatus=null)
    {
        $query = $this->repository->createQueryBuilder('c')
        ->join('c.communityUsers', 'cu')
        ->where('cu.user = :user')
        ->setParameter('user', $user);
        if (!is_null($proposalsHidden)) {
            $query->andWhere('c.proposalsHidden = :proposalsHidden')
            ->setParameter('proposalsHidden', $proposalsHidden);
        }
        if (!is_null($membersHidden)) {
            $query->andWhere('c.membersHidden = :membersHidden')
            ->setParameter('membersHidden', $membersHidden);
        }
        if (!is_null($memberStatus)) {
            $query->andWhere('cu.status = :memberStatus')
            ->setParameter('memberStatus', $memberStatus);
        }
        return $query->getQuery()->getResult();
    }
}
