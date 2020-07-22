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

namespace App\Communication\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Post a Message
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class MessageItemDataPersister implements ContextAwareDataPersisterInterface
{
    private $internalMessageManager;

    public function __construct(InternalMessageManager $internalMessageManager)
    {
        $this->internalMessageManager = $internalMessageManager;
    }
  
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Message && isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad message  id is provided"));
        }
        return $this->internalMessageManager->postMessage($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
