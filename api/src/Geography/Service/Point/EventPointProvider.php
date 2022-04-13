<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Geography\Service\Point;

use App\Event\Entity\Event;
use App\Event\Repository\EventRepository;
use App\Geography\Ressource\Point;

class EventPointProvider implements PointProvider
{
    protected $eventRepository;
    protected $maxResults;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    public function search(string $search): array
    {
        return $this->eventsToPoints(
            $this->eventRepository->findByNameAndStatus($search, Event::STATUS_ACTIVE)
        );
    }

    private function eventsToPoints(array $events): array
    {
        $points = [];
        foreach ($events as $event) {
            $points[] = $this->eventToPoint($event);
            if ($this->maxResults > 0 && count($points) == $this->maxResults) {
                break;
            }
        }

        return $points;
    }

    private function eventToPoint(Event $event): Point
    {
        $point = AddressAdapter::addressToPoint($event->getAddress());
        $point->setId((string) $event->getId());
        $point->setName($event->getName());
        $point->setType('event');

        return $point;
    }
}
