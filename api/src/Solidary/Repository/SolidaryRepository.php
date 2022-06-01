<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Repository;

use App\Action\Entity\Diary;
use App\Carpool\Entity\Proposal;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryUser;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Solidary::class);
    }


    public function find(int $id): ?Solidary
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }


    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Solidary
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get the Diaries entries of a Solidary
     *
     * @param Solidary $solidary   The Solidary
     * @return array|null
     */
    public function getDiaries(Solidary $solidary): ?array
    {
        $diaryRepository = $this->entityManager->getRepository(Diary::class);

        $query = $diaryRepository->createQueryBuilder('d')
        ->where('d.solidary = :solidary')
        ->setParameter('solidary', $solidary)
        ->orderBy('d.createdDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * Find the solidaries of a User
     *
     * @param User $user    The user
     * @return array|null
     */
    public function findByUser(User $user): ?array
    {
        $query = $this->repository->createQueryBuilder('s')
        ->join('s.solidaryUserStructure', 'sus')
        ->join('sus.solidaryUser', 'su')
        ->join('su.user', 'u')
        ->where('u.id = :user')
        ->setParameter('user', $user->getId());

        return $query->getQuery()->getResult();
    }

    /**
     * Find the solidary solutions of a solidary
     *
     * @param int $solidaryId Id of the Solidary
     * @return array|null
     */
    public function findSolidarySolutions(int $solidaryId): ?array
    {
        $query = $this->repository->createQueryBuilder('s')
        ->join('s.solidarySolutions', 'ss')
        ->where('s.id = :solidaryId')
        ->setParameter('solidaryId', $solidaryId);

        return $query->getQuery()->getResult();
    }

    /**
     * Find the solidaries link to a matching that include a solidaryUser
     *
     * @param SolidaryUser $solidaryUser
     * @return array|null
     */
    public function findBySolidaryUserMatching(SolidaryUser $solidaryUser): ?array
    {
        $query = $this->repository->createQueryBuilder('s')
        ->join('s.solidaryMatchings', 'sm')
        ->where('sm.solidaryUser = :solidaryUser')
        ->setParameter('solidaryUser', $solidaryUser);

        return $query->getQuery()->getResult();
    }

    /**
     * Get the potential solidary child of a solidary record
     *
     * @param Solidary $solidary    The solidary record
     * @return Solidary|null        The solidary child if found or null if not found
     */
    public function getChild(Solidary $solidary): ?Solidary
    {
        $query = $this->repository->createQueryBuilder('s')
        ->where('s.solidary = :solidary')
        ->setParameter('solidary', $solidary);

        return $query->getQuery()->getOneOrNullResult();
    }
}
