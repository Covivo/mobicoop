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

namespace App\Editorial\Admin\EditorialDataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Editorial\Entity\Editorial;
use App\Editorial\Admin\Service\EditorialManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Collection editorial data provider in admin context.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
final class EditorialCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $editorialManager;
    private $security;

    public function __construct(RequestStack $requestStack, EditorialManager $editorialManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->editorialManager = $editorialManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Editorial::class === $resourceClass && $operationName === "ADMIN_get";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        return $this->editorialManager->getEditorials($this->security->getUser());
    }
}
