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
 */

namespace App\Image\Admin\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Image\Admin\Service\IconManager;
use App\Image\Entity\Icon;

/**
 * Collection data provider for icons in administration context.
 */
final class IconCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $iconManager;

    public function __construct(IconManager $iconManager)
    {
        $this->iconManager = $iconManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Icon::class === $resourceClass && 'ADMIN_get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return $this->iconManager->getIcons();
    }
}
