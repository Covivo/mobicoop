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
 **************************/

namespace App\Import\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Import\Entity\UserImport;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Import\Service\ImportManager;

/**
 * Collection data provider for User import treatment.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
final class UserImportTreatCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    protected $importManager;
    
    public function __construct(RequestStack $requestStack, ImportManager $importManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->importManager = $importManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserImport::class === $resourceClass && $operationName === "treat";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return [$this->importManager->treatUserImport()];
    }
}
