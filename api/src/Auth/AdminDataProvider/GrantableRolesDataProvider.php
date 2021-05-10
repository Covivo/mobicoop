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

namespace App\Auth\AdminDataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Auth\Entity\Permission;
use App\Auth\ServiceAdmin\AuthManager;

/**
 * Get the grantable roles from an admin user to another user
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
final class GrantableRolesDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Permission::class === $resourceClass && $operationName === "ADMIN_grantable";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        return $this->authManager->getGrantable();
    }
}
