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
 */

namespace App\RelayPoint\Service;

use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\RelayPoint\Repository\RelayPointTypeRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Relay point manager.
 *
 * This service contains methods related to relay point management.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RelayPointManager
{
    private $entityManager;
    private $relayPointRepository;
    private $relayPointTypeRepository;
    private $logger;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RelayPointRepository $relayPointRepository,
        RelayPointTypeRepository $relayPointTypeRepository
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->relayPointRepository = $relayPointRepository;
        $this->relayPointTypeRepository = $relayPointTypeRepository;
    }

    /**
     * Get a relayPoint by its id.
     */
    public function getRelayPoint(int $id): ?RelayPoint
    {
        return $this->relayPointRepository->find($id);
    }

    /**
     * Get a relayPointType by its id.
     */
    public function getRelayPointType(int $id): ?RelayPointType
    {
        return $this->relayPointTypeRepository->find($id);
    }

    /**
     * Get the public relaypoints and some private if the current user is entitled to (i.e community...).
     *
     * @param User $user The User who make the request
     *
     * @return RelayPoint[]
     */
    public function getRelayPoints(User $user = null, string $operationName, array $context = []): array
    {
        return $this->relayPointRepository->findRelayPoints($user, $operationName, $context);
    }
}
