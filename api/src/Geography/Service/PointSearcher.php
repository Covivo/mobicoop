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

namespace App\Geography\Service;

use App\Community\Entity\CommunityUser;
use App\Event\Entity\Event;
use App\Event\Repository\EventRepository;
use App\Geography\Entity\Address;
use App\Geography\Ressource\Point;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;
use Exception;
use Symfony\Component\Security\Core\Security;

/**
 * Point searcher.
 */
class PointSearcher
{
    private const PROVIDER = 'MOBICOOP_API';

    private $geocoder;
    private $relayPointRepository;
    private $eventRepository;
    private $results;
    private $search;
    /**
     * @var null|User
     */
    private $user;
    private $maxRelayPointResults;
    private $maxEventResults;

    public function __construct(
        MobicoopGeocoder $mobicoopGeocoder,
        RelayPointRepository $relayPointRepository,
        EventRepository $eventRepository,
        Security $security,
        int $maxRelayPointResults,
        int $maxEventResults,
        array $prioritizeCentroid = null,
        array $prioritizeBox = null,
        string $prioritizeRegion = null
    ) {
        $this->results = [];
        $this->geocoder = $mobicoopGeocoder;
        $this->relayPointRepository = $relayPointRepository;
        $this->eventRepository = $eventRepository;
        $this->maxRelayPointResults = $maxRelayPointResults;
        $this->maxEventResults = $maxEventResults;
        if ($prioritizeCentroid) {
            $this->geocoder->setPrioritizeCentroid(
                $prioritizeCentroid['lon'],
                $prioritizeCentroid['lat']
            );
        }
        if ($prioritizeBox) {
            $this->geocoder->setPrioritizeBox(
                $prioritizeBox['minLon'],
                $prioritizeBox['minLat'],
                $prioritizeBox['maxLon'],
                $prioritizeBox['maxLat']
            );
        }
        if ($prioritizeRegion) {
            $this->geocoder->setPrioritizeRegion($prioritizeRegion);
        }
        $this->user = null;
        if ($security->getUser() instanceof User) {
            $this->user = $user = $security->getUser();
            /**
             * @var null|User $user
             */
            foreach ($user->getAddresses() as $address) {
                if ($address->isHome()) {
                    $this->geocoder->setPrioritizeCentroid(
                        $address->getLongitude(),
                        $address->getLatitude()
                    );

                    break;
                }
            }
        }
    }

    public function geocode(string $search): array
    {
        $this->search = $search;
        $this->addGeocoderResults();
        $this->addRelayPointResults();
        $this->addEventResults();

        return $this->results;
    }

    private function addGeocoderResults()
    {
        $results = [];

        try {
            $results = $this->geocoder->geocode($this->search);
        } catch (Exception $exception) {
        }

        $this->results = array_merge($this->results, $results);
    }

    private function addRelayPointResults()
    {
        $relayPoints = $this->relayPointRepository->findByNameAndStatus($this->search, RelayPoint::STATUS_ACTIVE);
        $relayPointResults = $this->relayPointsToPoints($relayPoints);
        $this->results = array_merge($this->results, $relayPointResults);
    }

    private function addEventResults()
    {
        $events = $this->eventRepository->findByNameAndStatus($this->search, Event::STATUS_ACTIVE);
        $eventResults = $this->eventsToPoints($events);
        $this->results = array_merge($this->results, $eventResults);
    }

    private function relayPointsToPoints(array $relayPoints): array
    {
        $results = [];
        foreach ($relayPoints as $relayPoint) {
            $userExcluded = false;
            if ($relayPoint->getCommunity() && $relayPoint->isPrivate()) {
                $userExcluded = true;
                if ($this->user) {
                    foreach ($relayPoint->getCommunity()->getCommunityUsers() as $communityUser) {
                        if (
                            $communityUser->getUser()->getId() == $this->user->getId()
                            && (
                                CommunityUser::STATUS_ACCEPTED_AS_MEMBER == $communityUser->getStatus()
                                || CommunityUser::STATUS_ACCEPTED_AS_MODERATOR == $communityUser->getStatus()
                            )
                        ) {
                            $userExcluded = false;

                            break;
                        }
                    }
                }
            }
            if (!$userExcluded) {
                $results[] = $this->relayPointToPoint($relayPoint);
                if (count($results) == $this->maxRelayPointResults) {
                    break;
                }
            }
        }

        return $results;
    }

    private function eventsToPoints(array $events): array
    {
        $results = [];
        foreach ($events as $event) {
            $results[] = $this->eventToPoint($event);
            if (count($results) == $this->maxEventResults) {
                break;
            }
        }

        return $results;
    }

    private function relayPointToPoint(RelayPoint $relayPoint): Point
    {
        $point = $this->addressToPoint($relayPoint->getAddress());
        $point->setId($relayPoint->getId());
        $point->setName($relayPoint->getName());
        $point->setType('relaypoint');

        return $point;
    }

    private function eventToPoint(Event $event): Point
    {
        $point = $this->addressToPoint($event->getAddress());
        $point->setId($event->getId());
        $point->setName($event->getName());
        $point->setType('event');

        return $point;
    }

    private function addressToPoint(Address $address): Point
    {
        $point = new Point();
        $point->setCountry($address->getAddressCountry());
        $point->setCountryCode($address->getCountryCode());
        $point->setHouseNumber($address->getHouseNumber());
        $point->setLat($address->getLatitude());
        $point->setLocality($address->getAddressLocality());
        $point->setLon($address->getLongitude());
        $point->setMacroRegion($address->getMacroRegion());
        $point->setPostalCode($address->getPostalCode());
        $point->setRegion($address->getRegion());
        $point->setStreetName($address->getStreet() ? $address->getStreet() : $address->getStreetAddress());
        $point->setProvider(self::PROVIDER);

        return $point;
    }
}
