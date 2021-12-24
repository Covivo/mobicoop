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

namespace App\User\DataProvider;

use App\User\Entity\User;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\User\Service\SsoManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class UserLogoutSsoCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $ssoManager;
    private $security;

    public function __construct(SsoManager $ssoManager, Security $security)
    {
        $this->security = $security;
        $this->ssoManager = $ssoManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && $operationName === "logoutSso";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): ?User
    {
        if (!($this->security->getUser() instanceof User)) {
            throw new \LogicException("Only a User can perform this action");
        }
        if ($user = $this->ssoManager->logoutSso($this->security->getUser())) {
            return $user;
        }
        return null;
    }
}
