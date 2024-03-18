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
use App\User\Ressource\UserUnderEighteen;
use App\User\Service\UserUnderEighteenManager;

/**
 * Item data provider for getting User under Eighteen.
 *
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
final class UserUnderEighteenItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $userUnderEighteenManager;

    public function __construct(UserUnderEighteenManager $userUnderEighteenManager)
    {
        $this->userUnderEighteenManager = $userUnderEighteenManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserUnderEighteen::class === $resourceClass && 'user_under_Eighteen_get_by_uuid' == $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?UserUnderEighteen
    {
        $userUnderEighteenManager = $this->userUnderEighteenManager->getUserUnderEighteenbyUuid($context['filters']['uuid']);
        if (is_null($userUnderEighteenManager)) {
            throw new \LogicException('User not found found');
        }

        return $userUnderEighteenManager;
    }
}
