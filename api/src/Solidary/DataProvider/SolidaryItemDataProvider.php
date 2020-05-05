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

namespace App\Solidary\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Solidary\Entity\Solidary;
use App\Solidary\Service\SolidaryManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidaryItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $solidaryManager;

    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Solidary::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Solidary
    {
        if ($operationName=="contactsList") {
            return $this->solidaryManager->getAsksList($id);
        }
        
        // Retrieve the blog post item from somewhere then return it or null if not found
        return $this->solidaryManager->getSolidary($id);
    }
}
