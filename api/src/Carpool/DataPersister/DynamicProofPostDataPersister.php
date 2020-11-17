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

 namespace App\Carpool\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Carpool\Ressource\DynamicProof;
use App\Carpool\Exception\DynamicException;
use App\Carpool\Service\DynamicManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

final class DynamicProofPostDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $dynamicManager;
    
    public function __construct(Security $security, DynamicManager $dynamicManager)
    {
        $this->security = $security;
        $this->dynamicManager = $dynamicManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof DynamicProof && isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        /**
         * @var DynamicProof $data
         */
        // we check if the request is sent by a real user
        if ($this->security->getUser() instanceof User) {
            $data->setUser($this->security->getUser());
        } else {
            throw new DynamicException("Operation not permitted");
        }
        return $this->dynamicManager->createDynamicProof($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
