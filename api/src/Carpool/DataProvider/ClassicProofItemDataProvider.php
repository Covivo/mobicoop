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

namespace App\Carpool\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Carpool\Ressource\ClassicProof;
use App\Carpool\Service\ProofManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Item data provider for Classic proof.
 */
final class ClassicProofItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    protected $proofManager;
    protected $request;

    public function __construct(ProofManager $proofManager, RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->proofManager = $proofManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ClassicProof::class === $resourceClass && $operationName === "get";
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        return $this->proofManager->getProof($this->request->get("id"), $this->request->query->get('filters'), $this->request->query->get('order'), $this->request->get("page"));
    }
}
