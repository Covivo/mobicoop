<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\CarpoolStandard\Service;

use App\CarpoolStandard\Entity\Booking;
use App\CarpoolStandard\Event\BookingReceivedEvent;
use App\Geography\Service\GeoSearcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class BookingManager
{
    private $carpoolStandardProvider;
    private $eventDispatcher;
    private $geoSearcher;

    public function __construct(
        CarpoolStandardProvider $carpoolStandardProvider,
        EventDispatcherInterface $eventDispatcher,
        GeoSearcher $geoSearcher
    ) {
        $this->carpoolStandardProvider = $carpoolStandardProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->geoSearcher = $geoSearcher;
    }

    public function postBooking(Booking $booking)
    {
        $this->carpoolStandardProvider->postBooking($booking);
    }

    public function treatExternalBooking(Booking $booking)
    {
        $this->reverseGeocodeAddresses($booking);

        var_dump($booking);

        exit;
        $event = new BookingReceivedEvent($booking);
        $this->eventDispatcher->dispatch(BookingReceivedEvent::NAME, $event);
    }

    public function reverseGeocodeAddresses(Booking $booking)
    {
        $reversedGeocodePickUpAddress = null;
        if ($foundAddresses = $this->geoSearcher->reverseGeoCode($booking->getPassengerPickupLat(), $booking->getPassengerPickupLng())) {
            $reversedGeocodePickUpAddress = $foundAddresses[0];
        }

        $reversedGeocodeDropOffAddress = null;
        if ($foundAddresses = $this->geoSearcher->reverseGeoCode($booking->getPassengerDropLat(), $booking->getPassengerDropLng())) {
            $reversedGeocodeDropOffAddress = $foundAddresses[0];
        }

        $booking->setPassengerPickupAddress($reversedGeocodePickUpAddress->getDisplayLabel());
        $booking->setPassengerDropAddress($reversedGeocodeDropOffAddress->getDisplayLabel());

        return $booking;
    }
}
