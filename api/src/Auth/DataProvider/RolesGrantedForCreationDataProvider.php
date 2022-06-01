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

namespace App\Auth\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Auth\Entity\Permission;
use App\Auth\Service\AuthManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Get the Roles granted for the current user, for user creation
 * TODO Move the function from AuthManager to PermissionManager, but first, clean PermissionManager.
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 */
final class RolesGrantedForCreationDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $authmanager;
    private $security;

    public function __construct(RequestStack $requestStack, Authmanager $authmanager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->authmanager = $authmanager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Permission::class === $resourceClass && 'roles_granted_for_creation' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        return $this->authmanager->getAuthItemsGrantedForCreation($this->security->getUser());
    }
}
