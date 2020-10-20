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

use App\User\Entity\User;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for User Login SSO return
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class UserSsoLoginReturnDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $userManager;
    private $request;

    public function __construct(UserManager $userManager, RequestStack $requestStack)
    {
        $this->userManager = $userManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && $operationName === "ssoLoginReturn";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        if ($this->request->get("id") == "") {
            throw new \LogicException("Missing Id");
        }
        
        return $this->userManager->treatSsoLoginReturn($this->request->get("id"));
    }
}
