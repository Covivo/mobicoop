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

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\User\Entity\User;
use App\User\Service\UserManager;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class UserCommunitiesCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $security;
    private $request;
    private $userManager;

    public function __construct(RequestStack $request, Security $security, UserManager $userManager)
    {
        $this->security = $security;
        $this->request = $request->getCurrentRequest();
        $this->userManager = $userManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && $operationName === "communities";
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        if (!($this->security->getUser() instanceof User)) {
            throw new \LogicException("Only a User can use this route");
        }
        return $this->userManager->getUserCommunities($this->security->getUser());
    }
}
