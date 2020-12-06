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

namespace App\User\AdminDataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\User;
use App\User\ServiceAdmin\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data persister for User in administration context
 */
final class UserDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $userManager;

    public function __construct(RequestStack $requestStack, UserManager $userManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'ADMIN_patch') {
            // for a patch operation, we update only some fields, we pass them to the method for further checkings
            $data = $this->userManager->patchUser($data, json_decode($this->request->getContent(), true));
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
