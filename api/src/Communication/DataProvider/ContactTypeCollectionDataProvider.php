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
 **************************/

namespace App\Communication\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Communication\Ressource\ContactType;
use App\Communication\Service\ContactManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for ContactType
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class ContactTypeCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $contactManager;

    public function __construct(RequestStack $requestStack, ContactManager $contactManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->contactManager = $contactManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ContactType::class === $resourceClass && $operationName === "get";
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return $this->contactManager->getContactTypes();
    }
}
