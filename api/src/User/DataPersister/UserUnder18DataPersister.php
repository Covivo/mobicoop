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
use App\User\Ressource\UserUnder18;
use App\User\Service\UserUnder18Manager;
use Symfony\Component\Security\Core\Security;

final class UserUnder18DataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $userUnder18Manager;

    public function __construct(Security $security, UserUnder18Manager $userUnder18Manager)
    {
        $this->security = $security;
        $this->userUnder18Manager = $userUnder18Manager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserUnder18 && isset($context['collection_operation_name']) && 'giveParentalConsent' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        return $this->userUnder18Manager->giveParentalConsent($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}