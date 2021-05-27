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

namespace App\User\Interoperability\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\App\Entity\App;
use App\User\Exception\BadRequestInteroperabilityUserException;
use App\User\Interoperability\Ressource\User;
use App\User\Interoperability\Service\UserManager;
use Symfony\Component\Security\Core\Security;

/**
 * Interoperability User DataProvider
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class UserItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $userManager;
    private $security;

    public function __construct(UserManager $userManager, Security $security)
    {
        $this->userManager = $userManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && ($operationName == "interop_get" || $operationName == "interop_put"  || $operationName == "interop_detach_sso");
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?User
    {
        if (!($this->security->getUser() instanceof App)) {
            throw new BadRequestInteroperabilityUserException(BadRequestInteroperabilityUserException::UNAUTHORIZED);
        }
        return $this->userManager->getUser($id);
    }
}
