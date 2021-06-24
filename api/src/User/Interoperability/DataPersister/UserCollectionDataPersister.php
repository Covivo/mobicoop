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

namespace App\User\Interoperability\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\App\Entity\App;
use App\User\Exception\BadRequestInteroperabilityUserException;
use App\User\Interoperability\Ressource\User;
use App\User\Interoperability\Service\UserManager;

final class UserCollectionDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $security;
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User && isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'interop_post';
    }

    public function persist($data, array $context = [])
    {
        if (!($this->security->getUser() instanceof App)) {
            throw new BadRequestInteroperabilityUserException(BadRequestInteroperabilityUserException::UNAUTHORIZED);
        }

        if (is_null($data)) {
            throw new BadRequestInteroperabilityUserException(BadRequestInteroperabilityUserException::NO_USER_PROVIDED);
        }

        if (!in_array($data->getGender(), User::GENDERS)) {
            throw new BadRequestInteroperabilityUserException(BadRequestInteroperabilityUserException::INVALID_GENDER);
        }

        return $this->userManager->registerUser($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
