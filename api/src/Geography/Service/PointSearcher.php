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
use App\Geography\Repository\AddressRepository;
use App\Geography\Ressource\Point;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Point searcher.
 */
class PointSearcher
{
    private const PROVIDER = 'MOBICOOP_API';

    private $geocoder;
    private $relayPointRepository;
    private $eventRepository;
    private $addressRepository;
    private $translator;
    private $points;
    private $search;
    /**
     * @var null|User
     */
    private $user;
    private $maxRelayPointResults;
    private $maxEventResults;
    private $maxUserResults;

    public function __construct(
        MobicoopGeocoder $mobicoopGeocoder,
        RelayPointRepository $relayPointRepository,
        EventRepository $eventRepository,
        AddressRepository $addressRepository,
        TranslatorInterface $translator,
        Security $security,
        int $maxRelayPointResults,
        int $maxEventResults,
        int $maxUserResults,
        array $prioritizeCentroid = null,
        array $prioritizeBox = null,
        string $prioritizeRegion = null
    ) {
        $this->points = [];
        $this->geocoder = $mobicoopGeocoder;
        $this->relayPointRepository = $relayPointRepository;
        $this->eventRepository = $eventRepository;
        $this->addressRepository = $addressRepository;
        $this->translator = $translator;
        $this->maxRelayPointResults = $maxRelayPointResults;
        $this->maxEventResults = $maxEventResults;
        $this->maxUserResults = $maxUserResults;
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
        $this->addUserResults();

        return $this->points;
    }

    private function addGeocoderResults()
    {
        $this->points = array_merge($this->points, $this->geocoder->geocode($this->search));
    }

    private function addRelayPointResults()
    {
        $relayPoints = $this->relayPointRepository->findByNameAndStatus($this->search, RelayPoint::STATUS_ACTIVE);
        $relayPointResults = $this->relayPointsToPoints($relayPoints);
        $this->points = array_merge($this->points, $relayPointResults);
    }

    private function addEventResults()
    {
        $events = $this->eventRepository->findByNameAndStatus($this->search, Event::STATUS_ACTIVE);
        $eventResults = $this->eventsToPoints($events);
        $this->points = array_merge($this->points, $eventResults);
    }

    private function addUserResults()
    {
        if ($this->user instanceof User) {
            $addresses = $this->addressRepository->findByName($this->translator->trans($this->search), $this->user->getId());
            $userResults = $this->addressesToPoints($addresses);
            $this->points = array_merge($this->points, $userResults);
        }
    }

    private function relayPointsToPoints(array $relayPoints): array
    {
        $points = [];
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
                $points[] = $this->relayPointToPoint($relayPoint);
                if (count($points) == $this->maxRelayPointResults) {
                    break;
                }
            }
        }

        return $points;
    }

    private function eventsToPoints(array $events): array
    {
        $points = [];
        foreach ($events as $event) {
            $points[] = $this->eventToPoint($event);
            if (count($points) == $this->maxEventResults) {
                break;
            }
        }

        return $points;
    }

    private function addressesToPoints(array $addresses): array
    {
        $points = [];
        foreach ($addresses as $address) {
            $point = $this->addressToPoint($address);
            $point->setId($address->getId());
            $point->setName($this->translator->trans($address->getName()));
            $point->setType('user');
            $points[] = $point;
            if (count($points) == $this->maxUserResults) {
                break;
            }
        }

        return $points;
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
