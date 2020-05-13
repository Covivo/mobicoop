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

namespace App\Match\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Match\Entity\Mass;
use App\Match\Service\MassMigrateManager;
use App\Match\Repository\MassRepository;
use App\Match\Exception\MassException;

/**
 * Item data provider for migrating a Mass
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class MassMigrateItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $massMigrateManager;
    private $massRepository;

    public function __construct(MassMigrateManager $massMigrateManager, MassRepository $massRepository)
    {
        $this->massMigrateManager = $massMigrateManager;
        $this->massRepository = $massRepository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Mass::class === $resourceClass && $operationName=="migrate";
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Mass
    {
        $mass = $this->massRepository->find($id);

        // Only qualified Masses can be migrated
        if ($mass->getMassType()!==1) {
            throw new MassException("bad Mass type");
        }

        return $this->massMigrateManager->migrate($mass);
    }
}
