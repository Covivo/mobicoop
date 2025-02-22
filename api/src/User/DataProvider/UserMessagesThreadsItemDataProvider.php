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

namespace App\User\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\User\Entity\User;
use App\User\Service\UserManager;

/**
 * Item data provider for getting User's solidaries
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class UserMessagesThreadsItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && ($operationName === "threadsDirectMessages" || $operationName === "threadsCarpoolMessages" || $operationName === "threadsSolidaryMessages");
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?User
    {
        $user = $this->userManager->getUser($id);
        
        if ($operationName === "threadsDirectMessages") {
            $user->setThreads($this->userManager->getThreadsDirectMessages($user));
        }
        if ($operationName === "threadsCarpoolMessages") {
            $user->setThreads($this->userManager->getThreadsCarpoolMessages($user));
        }
        if ($operationName === "threadsSolidaryMessages") {
            $user->setThreads($this->userManager->getThreadsSolidaryMessages($user));
        }

        return $user;
    }
}
