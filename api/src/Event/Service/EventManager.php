<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Event\Service;

use App\Event\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Event manager.
 *
 * This service contains methods related to event manipulations.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class EventManager
{
    private $entityManager;
    private $eventRepository;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Get an event by its id
     *
     * @param integer $eventId
     * @return Event|null
     */
    public function getEvent(int $eventId)
    {
        return $this->eventRepository->find($eventId);
    }
}
