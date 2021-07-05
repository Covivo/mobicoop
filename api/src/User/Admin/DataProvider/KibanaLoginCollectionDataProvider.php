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

namespace App\User\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Admin\Resource\KibanaLogin;
use App\User\Admin\Service\KibanaLoginManager;

/**
 * Collection data provider used to associate Users as deliveries for a campaign (depending on the filter type).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class KibanaLoginCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $kibanaLoginManager;

    public function __construct(KibanaLoginManager $kibanaLoginManager)
    {
        $this->kibanaLoginManager = $kibanaLoginManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return KibanaLogin::class === $resourceClass && $operationName === "ADMIN_kibana_logins";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        return $this->kibanaLoginManager->getKibanaLogins();
    }
}
