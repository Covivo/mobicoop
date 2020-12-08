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

namespace App\Event\Service;

use App\Event\Entity\Event;
use App\Event\Event\EventCreatedEvent;
use App\Event\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Event manager.
 *
 * This service contains methods related to event manipulations.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class EventManager
{
    private $eventRepository;
    private $dispatcher;
    private $entityManager;
    
    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository, EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Create an event
     *
     * @param Event $event  The event to create
     * @return Event        The event created
     */
    public function createEvent(Event $event)
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $eventEvent = new EventCreatedEvent($event);
        $this->dispatcher->dispatch($eventEvent, EventCreatedEvent::NAME);

        return $event;
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

    // very useful method :-|
    public function canReport()
    {
        // EVERYONE CAN REPORT EVENT
        return true;
    }

    /**
    * retrive events created by a user
    *
    * @param Int $userId
    * @return void
    */
    public function getCreatedEvents(Int $userId)
    {
        $createdEvents = $this->eventRepository->getCreatedEvents($userId);
        return $createdEvents;
    }


    /**
     * Generate the UrlKey of an Event
     *
     * @param Event $event
     * @return string The url key
     */
    public function generateUrlKey(Event $event): string
    {
        $urlKey = $event->getName();
        $urlKey = str_replace(" ", "-", $urlKey);
        $urlKey = str_replace("'", "-", $urlKey);
        $urlKey = strtr(utf8_decode($urlKey), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $urlKey = preg_replace('/[^A-Za-z0-9\-]/', '', $urlKey);

        return $urlKey;
    }
}
