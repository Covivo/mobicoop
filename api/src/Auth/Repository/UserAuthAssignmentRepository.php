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

namespace App\Auth\Repository;

use App\Auth\Entity\AuthItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Auth\Entity\UserAuthAssignment;
use App\User\Entity\User;

/**
 */
class UserAuthAssignmentRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(UserAuthAssignment::class);
    }

    /**
     * Find Auth Item Assignment by AuthItem and User.
     *
     * @param AuthItem $authItem    The auth item
     * @param User $user            The user
     * @return array
     */
    public function findByAuthItemAndUser(AuthItem $authItem, User $user): array
    {
        return $this->repository->findBy(['authItem'=>$authItem,'user'=>$user]);
    }

    /**
     * Find Auth Item Assignment by User.
     *
     * @param User $user            The user
     * @return array
     */
    public function findByUser(User $user): array
    {
        return $this->repository->findBy(['user'=>$user]);
    }
}
