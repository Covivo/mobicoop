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

namespace App\Solidary\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Solidary\Entity\Structure;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\StructureManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class StructureGeolocationCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $structureManager;
    private $context;

    public function __construct(StructureManager $structureManager)
    {
        $this->structureManager = $structureManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;

        return Structure::class === $resourceClass && 'structure_geolocation' == $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if (!isset($this->context['filters'])) {
            throw new SolidaryException(SolidaryException::INVALID_DATA_PROVIDED);
        }

        if (!isset($this->context['filters']['lat'])) {
            throw new SolidaryException(SolidaryException::MISSING_LATITUDE);
        }

        if (!isset($this->context['filters']['lon'])) {
            throw new SolidaryException(SolidaryException::MISSING_LONGITUDE);
        }

        return $this->structureManager->getGeolocalisedStructures($this->context['filters']['lat'], $this->context['filters']['lon']);
    }
}
