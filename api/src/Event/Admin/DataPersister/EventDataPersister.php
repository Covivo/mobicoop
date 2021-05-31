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

namespace App\Event\Admin\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Event\Admin\Service\EventManager;
use App\Event\Entity\Event;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data persister for Event in administration context
 */
final class EventDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $eventManager;

    public function __construct(RequestStack $requestStack, EventManager $eventManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->eventManager = $eventManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Event && ((isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'ADMIN_post') || (isset($context['item_operation_name']) && $context['item_operation_name'] == 'ADMIN_patch' || $context['item_operation_name'] == 'ADMIN_delete'));
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'ADMIN_post') {
            $data = $this->eventManager->addEvent($data);
        } elseif (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'ADMIN_patch') {
            // for a patch operation, we update only some fields, we pass them to the method for further checkings
            $data = $this->eventManager->patchEvent($data, json_decode($this->request->getContent(), true));
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        if (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'ADMIN_delete') {
            return $this->eventManager->deleteEvent($data);
        }
    }
}
