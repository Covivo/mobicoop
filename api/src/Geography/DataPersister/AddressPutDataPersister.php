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
  */

namespace App\Geography\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Geography\Entity\Address;
use App\Geography\Service\AddressManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class AddressPutDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $request;
    private $addressManager;

    public function __construct(Security $security, AddressManager $addressManager, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
        $this->addressManager = $addressManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Address && isset($context['item_operation_name']) && 'put' == $context['item_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        return $this->addressManager->updateAddress($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
