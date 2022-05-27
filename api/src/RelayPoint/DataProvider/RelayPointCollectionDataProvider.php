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

namespace App\RelayPoint\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Service\RelayPointManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * @author maxime.bardot <maxime.bardot@mobicoop.org>
 */
final class RelayPointCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $relayPointManager;
    private $security;

    public function __construct(Security $security, RelayPointManager $relayPointManager)
    {
        $this->security = $security;
        $this->relayPointManager = $relayPointManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return RelayPoint::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $user = null;

        if ($this->security->getUser() instanceof User) {
            $user = $this->security->getUser();
        }

        return $this->relayPointManager->getRelayPoints($user, $operationName, $context);
    }
}
