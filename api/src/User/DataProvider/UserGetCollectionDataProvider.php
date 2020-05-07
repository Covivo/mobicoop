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

namespace App\User\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data provider for User on GET, use for check if we come from admin ( $this->request->get('accesFromAdminReact') )
 * Here we want to GET the list of users who are in the communities that belong to the current user
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 *
 */
final class UserGetCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $userManager;
    private $security;

    public function __construct(RequestStack $requestStack, UserManager $userManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && $operationName === "accessAdmin";
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return $this->userManager->getUsersForAdmin($this->security->getUser());
    }
}
