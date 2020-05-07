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

namespace App\Match\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Match\Entity\Mass;
use App\Match\Exception\MassException;
use App\Match\Service\MassMigrateManager;

/**
 * Item data persister for migrating a Mass
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class MassMigrateDataPersister implements ContextAwareDataPersisterInterface
{
    private $massMigrateManager;

    public function __construct(MassMigrateManager $massMigrateManager)
    {
        $this->massMigrateManager = $massMigrateManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Mass && isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'migrate';
    }

    public function persist($data, array $context = [])
    {
        // Only qualified Masses can be migrated
        if ($data->getMassType()!==1) {
            throw new MassException("bad Mass type");
        }

        return $this->massMigrateManager->migrate($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
