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

namespace App\Event\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Event\Entity\Event;
use App\Event\Service\EventManager;

final class EventPostDataPersister implements ContextAwareDataPersisterInterface
{
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function supports($data, array $context = []): bool
    {
        if ($data instanceof Event) {
            switch ($context) {
                case isset($context['collection_operation_name']):
                    return 'post' == $context['collection_operation_name'];

                    break;

                case isset($context['item_operation_name']):
                    return 'delete' == $context['item_operation_name'];

                    break;

                default:
                    return false;

                    break;
            }
        } else {
            return false;
        }
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name']) {
            return $this->eventManager->createEvent($data);
        }
    }

    public function remove($data, array $context = [])
    {
        if (isset($context['item_operation_name']) && 'delete' == $context['item_operation_name']) {
            return $this->eventManager->deleteEvent($data);
        }
    }
}
