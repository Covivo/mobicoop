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
use Symfony\Component\Security\Core\Security;

final class UserItemDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $userManager;

    public function __construct(UserManager $userManager, Security $security)
    {
        $this->userManager = $userManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User && isset($context['item_operation_name']) && ($context['item_operation_name'] == 'interop_put' || $context['item_operation_name'] == 'interop_detach_sso');
    }

    public function persist($data, array $context = [])
    {
        if (!($this->security->getUser() instanceof App)) {
            throw new BadRequestInteroperabilityUserException(BadRequestInteroperabilityUserException::UNAUTHORIZED);
        }

        if ($context['item_operation_name'] == 'interop_put') {
            return $this->userManager->updateUser($data);
        }
        if ($context['item_operation_name'] == 'interop_detach_sso') {
            return "interop_detach_sso";
        }
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
