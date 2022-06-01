<?php
/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * SolidaryAskHistory Repository
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryAskHistoryRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(SolidaryAskHistory::class);
    }

    public function find(int $id): ?SolidaryAskHistory
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

    public function findOneBy(array $criteria): ?SolidaryAskHistory
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find the last solidary ask history for a given solidary ask
     *
     * @param SolidaryAsk $solidaryAsk  The solidary ask
     * @return SolidaryAskHistory       The last solidary ask history
     */
    public function findLastSolidaryAskHistory(SolidaryAsk $solidaryAsk): SolidaryAskHistory
    {
        $query = $this->repository->createQueryBuilder('sah')
        ->join('sah.solidaryAsk', 'sa')
        ->where('sa = :solidaryAsk')
        ->setParameter('solidaryAsk', $solidaryAsk)
        ->orderBy('sah.createdDate', 'DESC')
        ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find the last SolidaryAskHistory having a linked message
     *
     * @param SolidaryAsk $solidaryAsk  The solidary ask
     * @return SolidaryAskHistory|null  The solidary ask history found
     */
    public function findLastSolidaryAskHistoryWithMessage(SolidaryAsk $solidaryAsk): ?SolidaryAskHistory
    {
        $query = $this->repository->createQueryBuilder('sah')
        ->join('sah.solidaryAsk', 'sa')
        ->join('sah.message', 'm')
        ->where('sa = :solidaryAsk')
        ->setParameter('solidaryAsk', $solidaryAsk)
        ->orderBy('sah.createdDate', 'DESC')
        ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }
}
