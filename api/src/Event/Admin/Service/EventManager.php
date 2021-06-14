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

namespace App\Event\Admin\Service;

use App\Event\Entity\Event;
use App\Event\Exception\EventException;
use App\Event\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Geography\Entity\Address;
use App\User\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Event manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class EventManager
{
    private $entityManager;
    private $userRepository;
    private $eventRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EventRepository $eventRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Add an event.
     *
     * @param Event     $event              The event to add
     * @return Event    The event created
     */
    public function addEvent(Event $event)
    {
        if ($creator = $this->userRepository->find($event->getCreatorId())) {
            $event->setUser($creator);
        } else {
            throw new EventException("creator not found");
        }

        // persist the event
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        // check if the address was set
        if (!is_null($event->getAddress())) {
            $address = new Address();
            $address->setStreetAddress($event->getAddress()->getStreetAddress());
            $address->setPostalCode($event->getAddress()->getPostalCode());
            $address->setAddressLocality($event->getAddress()->getAddressLocality());
            $address->setAddressCountry($event->getAddress()->getAddressCountry());
            $address->setLatitude($event->getAddress()->getLatitude());
            $address->setLongitude($event->getAddress()->getLongitude());
            $address->setHouseNumber($event->getAddress()->getHouseNumber());
            $address->setStreetAddress($event->getAddress()->getStreetAddress());
            $address->setSubLocality($event->getAddress()->getSubLocality());
            $address->setLocalAdmin($event->getAddress()->getLocalAdmin());
            $address->setCounty($event->getAddress()->getCounty());
            $address->setMacroCounty($event->getAddress()->getMacroCounty());
            $address->setRegion($event->getAddress()->getRegion());
            $address->setMacroRegion($event->getAddress()->getMacroRegion());
            $address->setCountryCode($event->getAddress()->getCountryCode());
            $address->setEvent($event);
            $this->entityManager->persist($address);
            $this->entityManager->flush();
        }

        return $event;
    }

    /**
     * Patch an event.
     *
     * @param Event $event  The event to update
     * @param array $fields The updated fields
     * @return Event        The event updated
     */
    public function patchEvent(Event $event, array $fields)
    {
        // check if creator has changed
        if (in_array('creatorId', array_keys($fields))) {
            if ($creator = $this->userRepository->find($fields['creatorId'])) {
                // set the new creator
                $event->setUser($creator);
            } else {
                throw new EventException("Creator not found");
            }
        }

        // persist the event
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        
        // return the event
        return $event;
    }

    /**
     * Delete an event
     *
     * @param Event $event  The event to delete
     * @return void
     */
    public function deleteEvent(Event $event)
    {
        $this->entityManager->remove($event);
        $this->entityManager->flush();
    }

    /**
     * Get internal events (exclude external events)
     *
     * @return void
     */
    public function getInternalEvents()
    {
        return $this->eventRepository->getInternalEvents();
    }

    /**
     * Get internal events QueryBuilder (exclude external events)
     * It's used to get only the querybuilder to apply filters on it on custom DataProvider
     *
     * @return QueryBuilder
     */
    public function getInternalEventsQueryBuilder(): QueryBuilder
    {
        return $this->eventRepository->getInternalEventsQueryBuilder();
    }
}
