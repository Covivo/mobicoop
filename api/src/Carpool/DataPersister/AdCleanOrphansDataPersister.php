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
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\ProposalManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

final class AdCleanOrphansDataPersister implements ContextAwareDataPersisterInterface
{
    private $proposalManager;
    private $security;

    public function __construct(ProposalManager $proposalManager, Security $security)
    {
        $this->proposalManager = $proposalManager;
        $this->security = $security;
    }
  
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Ad && isset($context['collection_operation_name']) && $context['collection_operation_name'] === 'cleanOrphans';
    }

    public function persist($data, array $context = [])
    {
        if (!($this->security->getUser() instanceof User)) {
            throw new \LogicException("Only a User can perform this action");
        }
        
        return $this->proposalManager->cleanUserOrphanProposals($this->security->getUser());
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
