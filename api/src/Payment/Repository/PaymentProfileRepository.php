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
 */

namespace App\Payment\Repository;

use App\Payment\Entity\PaymentProfile;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class PaymentProfileRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $logger;

    private $paymentProviderService;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, string $paymentProviderService)
    {
        $this->repository = $entityManager->getRepository(PaymentProfile::class);
        $this->logger = $logger;
        $this->paymentProviderService = $paymentProviderService;
    }

    public function find(int $id): ?PaymentProfile
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): ?array
    {
        if (!in_array('provider', $criteria)) {
            $criteria['provider'] = $this->paymentProviderService;
        }

        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?PaymentProfile
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findAllIdentifiers(): ?array
    {
        $query = $this->repository->createQueryBuilder('pp')
            ->select('pp.id', 'pp.identifier', 'pp.validationId')
        ;

        return $query->getQuery()->getResult();
    }

    public function findPaymentProfileByUserInfos(User $user, string $paymentProvider): ?array
    {
        $query = $this->repository->createQueryBuilder('pp')
            ->join('pp.user', 'u')
            ->where('u.givenName = :givenName and u.familyName = :familyName and u.birthDate = :birthDate and pp.provider = :paymentProvider')
            ->setParameter('givenName', $user->getGivenName())
            ->setParameter('familyName', $user->getFamilyName())
            ->setParameter('birthDate', $user->getBirthDate())
            ->setParameter('paymentProvider', $paymentProvider)
        ;

        return $query->getQuery()->getResult();
    }
}
