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

namespace App\Geography\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Geography\Entity\Completion;

/**
 * Collection data provider for Completion entity.
 *
 * Automatically associated to Completion entity thanks to autowiring (see 'supports' method).
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 *
 */
final class CompletionCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Completion::class === $resourceClass;
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {

        $data = file_get_contents('https://maps.googleapis.com/maps/api/place/autocomplete/json?input=1600+Amphitheatre&key=%3CAPI_KEY%3E&sessiontoken=1234567890');
        return json_decode($data,true);
    }
}
