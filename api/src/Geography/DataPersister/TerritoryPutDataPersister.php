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

namespace App\Geography\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Geography\Entity\Territory;
use App\Geography\Service\TerritoryManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class TerritoryPutDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $request;
    private $territoryManager;

    public function __construct(Security $security, TerritoryManager $territoryManager, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
        $this->territoryManager = $territoryManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Territory && isset($context['item_operation_name']) && 'put' == $context['item_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        return $this->territoryManager->updateTerritory($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
