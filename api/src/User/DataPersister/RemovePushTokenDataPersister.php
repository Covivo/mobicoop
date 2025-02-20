<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\User\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class RemovePushTokenDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $security;
    private $userManager;

    public function __construct(RequestStack $requestStack, Security $security, UserManager $userManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->userManager = $userManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User && $this->request->isMethod('PUT') && 'remove_push_token' == $context['item_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        if (!$this->security->getUser() instanceof User) {
            return $data;
        }

        $this->userManager->removePushToken($this->security->getUser());

        return $this->security->getUser();
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
