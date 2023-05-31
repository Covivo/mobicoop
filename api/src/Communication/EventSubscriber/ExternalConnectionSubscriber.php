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
use App\ExternalJourney\Event\ExternalConnectionConfirmedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class ExternalConnectionSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $loggedUser;

    public function __construct(NotificationManager $notificationManager, Security $security)
    {
        $this->notificationManager = $notificationManager;
        $this->loggedUser = $security->getUser();
    }

    public static function getSubscribedEvents()
    {
        return [
            ExternalConnectionConfirmedEvent::NAME => 'onExternalConnectionConfirmed',
        ];
    }

    public function onExternalConnectionConfirmed(ExternalConnectionConfirmedEvent $event)
    {
        $this->notificationManager->notifies(ExternalConnectionConfirmedEvent::NAME, $this->loggedUser, $event->getExternalConnection());
    }
}
