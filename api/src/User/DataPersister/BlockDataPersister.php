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
use App\User\Entity\User;
use App\User\Exception\BlockException;
use App\User\Ressource\Block;
use App\User\Service\BlockManager;
use LogicException;
use Symfony\Component\Security\Core\Security;

final class BlockDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $blockManager;

    public function __construct(Security $security, BlockManager $blockManager)
    {
        $this->security = $security;
        $this->blockManager = $blockManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Block && isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        if (!($this->security->getUser() instanceof User)) {
            throw new \LogicException("Only a User can perform this action");
        }

        return $this->blockManager->handleBlock($this->security->getUser(), $data->getUser());
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
