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
 */

namespace App\User\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class UserDataPersister implements ContextAwareDataPersisterInterface
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
        return $data instanceof User;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad user id is provided'));
        }
        if (isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name']) {
            $data = $this->userManager->registerUser($data, true);
        } elseif (isset($context['collection_operation_name']) && 'userRegistration' == $context['collection_operation_name']) {
            $data = $this->userManager->registerUser($data);
        } elseif (isset($context['item_operation_name']) && 'put' == $context['item_operation_name']) {
            $data = $this->userManager->updateUser($data);
        } elseif (isset($context['item_operation_name']) && 'updateLanguage' == $context['item_operation_name']) {
            $body = json_decode($this->request->getContent(), true);
            if (isset($body['language'], $body['language']['code'])) {
                $data = $this->userManager->updateLanguage($data, $body['language']['code']);
            }
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
