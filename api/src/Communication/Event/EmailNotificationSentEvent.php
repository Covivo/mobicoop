<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Communication\Event;

use App\Communication\Entity\Email;
use App\Communication\Entity\Medium;
use App\Communication\Entity\Notification;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when an email notification is sent.
 */
class EmailNotificationSentEvent extends Event
{
    public const NAME = 'communication_email_notification_sent';

    protected $email;
    protected $notification;
    protected $medium;

    public function __construct(Email $email, Notification $notification, Medium $medium)
    {
        $this->email = $email;
        $this->notification = $notification;
        $this->medium = $medium;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getNotification()
    {
        return $this->notification;
    }

    public function getMedium()
    {
        return $this->medium;
    }
}
