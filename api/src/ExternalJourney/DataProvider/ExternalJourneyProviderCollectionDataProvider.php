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

namespace App\ExternalJourney\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;


use App\ExternalJourney\Entity\ExternalJourneyProvider;

/**
 * Collection data provider for External Journey Provider entity.
 *
 * Automatically associated to External Journey Provider entity thanks to autowiring (see 'supports' method).
 *
 * @author Sofiane Belaribi <sofiane.belaribi@covivo.eu>
 *
 */
final class ExternalJourneyProviderCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private const EXTERNAL_JOURNEY_CONFIG_FILE = "../config.json";
    private const EXTERNAL_JOURNEY_API_KEY = "rdexApi";
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExternalJourneyProvider::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        if (file_exists(self::EXTERNAL_JOURNEY_CONFIG_FILE)) {
            $apiList = json_decode(file_get_contents(self::EXTERNAL_JOURNEY_CONFIG_FILE), true);
            $rdexApi = array_keys($apiList[self::EXTERNAL_JOURNEY_API_KEY]);
            return $rdexApi;
        }
        return [];
    }
}
