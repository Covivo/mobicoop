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

namespace App\User\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\IdentityProof;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use App\User\Service\IdentityProofManager;

final class IdentityProofDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $security;
    private $identityProofManager;

    public function __construct(RequestStack $requestStack, Security $security, IdentityProofManager $identityProofManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->identityProofManager = $identityProofManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof IdentityProof;
    }

    public function persist($data, array $context = [])
    {
        var_dump('ici');
        var_dump($data);
        die;
        // call your persistence layer to save $data
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->identityProofManager->createIdentityProof($data);
        } elseif (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'put') {
            $data = $this->identityProofManager->updateIdentityProof($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
