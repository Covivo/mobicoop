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

use App\Event\Entity\Event;
use App\Event\Event\ValidateCreateEventEvent;
use App\Event\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private $dispatcher;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository, EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->eventDispatcher = $dispatcher;
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

    public function canReport()
    {
        // EVERYONE CAN REPORT EVENT
        return true;
    }


    //Send and email to the owner of the event
    public function sendValidateEmail(int $id)
    {
        $event = $this->getEvent($id);
        $eventEvent = new ValidateCreateEventEvent($event);
        $this->eventDispatcher->dispatch($eventEvent, ValidateCreateEventEvent::NAME);

        return $event;
    }
}
