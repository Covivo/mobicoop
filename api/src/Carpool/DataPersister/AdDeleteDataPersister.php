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
use App\Carpool\Entity\Proposal;
use App\Carpool\Service\ProposalManager;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdDeleteDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $proposalManager;

    public function __construct(ProposalManager $proposalManager, RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->proposalManager = $proposalManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Proposal && isset($context['item_operation_name']) && 'delete' === $context['item_operation_name'];
    }

    public function persist($data, array $context = [])
    {
    }

    public function remove($data, array $context = [])
    {
        return $this->proposalManager->deleteProposal($data, json_decode($this->request->getContent(), true));
    }
}
