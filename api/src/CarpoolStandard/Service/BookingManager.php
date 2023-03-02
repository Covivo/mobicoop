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
use App\Event\Event\BookingReceivedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class BookingManager
{
    private $carpoolStandardProvider;
    private $eventDispatcher;

    public function __construct(CarpoolStandardProvider $carpoolStandardProvider, EventDispatcherInterface $eventDispatcher)
    {
        $this->carpoolStandardProvider = $carpoolStandardProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postBooking(Booking $booking)
    {
        $this->carpoolStandardProvider->postBooking($booking);
    }

    public function treatExternalBooking(Booking $booking)
    {
        $event = new BookingReceivedEvent($booking);
        $this->eventDispatcher->dispatch(BookingReceivedEvent::NAME, $event);
    }
}
