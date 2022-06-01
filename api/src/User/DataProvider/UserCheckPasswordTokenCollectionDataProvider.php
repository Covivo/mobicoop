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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Item data provider for User me.
 *
 * @author Celine Jacquet <celine.jacquet@mobicoop.org>
 *
 */
final class UserCheckPasswordTokenCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $userManager;
    private $request;
    private $translator;

    public function __construct(UserManager $userManager, RequestStack $request, TranslatorInterface $translator)
    {
        $this->userManager = $userManager;
        $this->request = $request->getCurrentRequest();
        $this->translator = $translator;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && $operationName === "checkPasswordToken";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        if (
            is_null($this->request->get("pwdToken"))
        ) {
            return null;
        }
        return $this->userManager->checkPasswordToken(
            $this->request->get("pwdToken")
        );
    }
}
