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
 */

namespace App\Communication\EventSubscriber;

use App\Communication\Service\NotificationManager;
use App\Event\Event\BookingReceivedEvent;
use App\User\Service\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $userManager;

    public function __construct(NotificationManager $notificationManager, UserManager $userManager)
    {
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            BookingReceivedEvent::NAME => 'onBookingReceived',
        ];
    }

    public function onBookingReceived(BookingReceivedEvent $event)
    {
        $this->notificationManager->notifies(BookingReceivedEvent::NAME, $this->userManager->getUser((int) $event->getBooking()->getPassenger()->getId()), $event->getBooking());
    }
}
