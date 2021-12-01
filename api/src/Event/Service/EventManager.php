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

namespace App\Event\Service;

use App\App\Repository\AppRepository;
use App\DataProvider\Entity\ApidaeProvider;
use App\Event\Entity\Event;
use App\Event\Event\EventCreatedEvent;
use App\Event\Repository\EventRepository;
use App\Action\Repository\ActionRepository;
use App\DataProvider\Entity\TourinsoftProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Geography\Service\GeoTools;
use Doctrine\ORM\QueryBuilder;
use Exception;

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
    private $geoTools;
    private $provider;
    private $appRepository;

    const EVENT_PROVIDER_APIDAE = 'apidae';
    const EVENT_PROVIDER_TOURINSOFT = 'tourinsoft';
    const APP_ID = 1;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        EventDispatcherInterface $dispatcher,
        GeoTools $geoTools,
        AppRepository $appRepository,
        String $eventProvider,
        String $eventProviderApiKey,
        String $eventProviderProjectId,
        String $eventProviderSelectionId
    ) {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->dispatcher = $dispatcher;
        $this->geoTools = $geoTools;
        $this->eventProvider = $eventProvider;
        $this->eventProviderApiKey = $eventProviderApiKey;
        $this->eventProviderProjectId = $eventProviderProjectId;
        $this->eventProviderSelectionId = $eventProviderSelectionId;
        $this->appRepository = $appRepository;
        switch ($eventProvider) {
            case self::EVENT_PROVIDER_APIDAE:
                $this->provider = new ApidaeProvider($this->eventProviderApiKey, $this->eventProviderProjectId, $this->eventProviderSelectionId);
                break;
            case self::EVENT_PROVIDER_TOURINSOFT:
                $this->provider = new TourinsoftProvider();
                break;
        }
    }

    /**
     * Create an event
     *
     * @param Event $event  The event to create
     * @return Event        The event created
     */
    public function createEvent(Event $event)
    {
        if (is_null($event->getUser()) && is_null($event->getApp())) {
            throw new Exception("User or App are mandatory", 1);
        }
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        // We set the displayLabel of the event's address
        $event->getAddress()->setDisplayLabel($this->geoTools->getDisplayLabel($event->getAddress()));
        // we set the urlKey
        $event->setUrlKey($this->generateUrlKey($event));

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

    public function getEvents(): QueryBuilder
    {
        return $this->eventRepository->getEvents();
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

        // We don't want to finish with a single "-"
        if (substr($urlKey, -1) == "-") {
            $urlKey = substr($urlKey, 0, strlen($urlKey) - 1);
        }

        return $urlKey;
    }

    /**
     * method to import external events
     *
     * @return void
     */
    public function importEvents()
    {
        $eventsToImport = $this->provider->getEvents();

        foreach ($eventsToImport as $eventToImport) {
            $event = $this->eventRepository->findOneBy(["externalId" => $eventToImport->getExternalId(), "externalSource" => $eventToImport->getExternalSource()]);
            if (isset($event) && !is_null($event)) {
                $event->setName($eventToImport->getName());
                $event->setFromDate($eventToImport->getFromDate());
                $event->setToDate($eventToImport->getToDate());
                $event->setDescription($eventToImport->getDescription());
                $event->setFullDescription($eventToImport->getFullDescription());
                $event->setAddress($eventToImport->getAddress());
                $event->setUrl($eventToImport->getUrl());
                $event->setExternalImageUrl($eventToImport->getExternalImageUrl());
            } else {
                $event = new Event();
                $event->setExternalId($eventToImport->getExternalId());
                $event->setExternalSource($eventToImport->getExternalSource());
                $event->setName($eventToImport->getName());
                $event->setFromDate($eventToImport->getFromDate());
                $event->setToDate($eventToImport->getToDate());
                $event->setDescription($eventToImport->getDescription());
                $event->setFullDescription($eventToImport->getFullDescription());
                $event->setAddress($eventToImport->getAddress());
                $event->setUrl($eventToImport->getUrl());
                $event->setExternalImageUrl($eventToImport->getExternalImageUrl());
                $event->setStatus(1);
                $event->setPrivate(0);
                $event->setUseTime(0);
                $event->setApp($this->appRepository->find(self::APP_ID));
            }

            if (is_null($event->getUser()) && is_null($event->getApp())) {
                throw new Exception("User or App are mandatory", 1);
            }
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }
        return;
    }
}
