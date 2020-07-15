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

namespace App\Payment\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Payment\Entity\PaymentProfile;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class PaymentProfileRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->repository = $entityManager->getRepository(PaymentProfile::class);
        $this->logger = $logger;
    }

    public function find(int $id): ?PaymentProfile
    {
        return $this->repository->find($id);
    }

    /**
     * Find All the PaymentProfile by criteria
     *
     * @return User|null
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?PaymentProfile
    {
        $user = $this->repository->findOneBy($criteria);
        return $user;
    }
}
