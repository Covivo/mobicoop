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

namespace App\User\Repository;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Community\Entity\Community;
use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Exception\SolidaryException;
use Doctrine\ORM\EntityRepository;

class UserRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(User::class);
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * Find All the users
     *
     * @return User|null
     */
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function findOneBy(array $criteria): ?User
    {
        $user = $this->repository->findOneBy($criteria);
        return $user;
    }

    /**
     * Get all the users in the communities given
     *
     * @param Community $community
     * @return User|null
     */
    public function getUserBelongToMyCommunity(Community $community)
    {
        return $this->repository->createQueryBuilder('u')
          ->leftJoin('u.communityUsers', 'c')
          ->andWhere("c.community = :community")
          ->setParameter('community', $community)
          ->getQuery()
          ->getResult();
    }

    /**
     * Get Users with a specific type of SolidaryUser
     *
     * @param string $type Type of SolidaryUser (Beneficiary or Volunteer)
     * @return array|null
     */
    public function findUsersBySolidaryUserType(string $type=null): ?array
    {
        $query = $this->repository->createQueryBuilder('u')
        ->join('u.solidaryUser', 'su');

        if ($type==SolidaryBeneficiary::TYPE) {
            $query->where('su.beneficiary = true');
        } elseif ($type==SolidaryVolunteer::TYPE) {
            $query->where('su.volunteer = true');
        } else {
            throw new SolidaryException(SolidaryException::TYPE_SOLIDARY_USER_UNKNOWN);
        }

        return $query->getQuery()->getResult();
    }
}
