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

namespace App\Carpool\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Carpool\Exception\AdException;
use App\Carpool\Ressource\ClassicProof;
use App\Carpool\Service\AdManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

final class CarpoolProofPostDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $adManager;

    public function __construct(Security $security, AdManager $adManager)
    {
        $this->security = $security;
        $this->adManager = $adManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof ClassicProof && isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        // @var ClassicProof $data
        // we check if the request is sent by a real user
        if ($this->security->getUser() instanceof User) {
            $data->setUser($this->security->getUser());
        } else {
            throw new AdException('Operation not permitted');
        }

        return $this->adManager->createCarpoolProof($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
