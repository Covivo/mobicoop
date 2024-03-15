<?php
/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\User\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Entity\User;
use App\User\Ressource\UserUnder18;
use App\User\Service\UserUnder18Manager;

/**
 * Item data provider for getting User under 18.
 *
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
final class UserUnder18ItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $userUnder18Manager;

    public function __construct(UserUnder18Manager $userUnder18Manager)
    {
        $this->userUnder18Manager = $userUnder18Manager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserUnder18::class === $resourceClass && 'user_under_18_get_by_uuid' == $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?UserUnder18
    {
        var_dump('ici');

        exit;
        $userUnder18Manager = $this->userUnder18Manager->getUserUnder18byUuid($context['filters']['id']);
        if (is_null($userUnder18Manager)) {
            throw new \LogicException('User not found found');
        }

        return $userUnder18Manager;
    }
}
