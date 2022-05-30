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

namespace App\Communication\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Communication\Service\NotificationManager;
use App\Match\Event\MassAnalyzeErrorsEvent;
use App\Match\Event\MassMatchedEvent;
use App\User\Service\UserManager;
use App\Match\Event\MassMigrateUserMigratedEvent;
use App\Match\Event\MassPublicTransportSolutionsGatheredEvent;

class MassSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $userManager;

    public function __construct(NotificationManager $notificationManager, UserManager $userManager)
    {
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MassMigrateUserMigratedEvent::NAME => 'onMassMigrateUserMigrated',
            MassAnalyzeErrorsEvent::NAME => 'onMassAnalyzeErrors',
            MassMatchedEvent::NAME => 'onMassMatched',
            MassPublicTransportSolutionsGatheredEvent::NAME => 'onMassPublicTransportSolutionsGathered'
        ];
    }

    public function onMassMigrateUserMigrated(MassMigrateUserMigratedEvent $event)
    {
        $this->notificationManager->notifies(MassMigrateUserMigratedEvent::NAME, $event->getMassPerson()->getUser(), $event->getMassPerson());
    }

    public function onMassAnalyzeErrors(MassAnalyzeErrorsEvent $event)
    {
        $this->notificationManager->notifies(MassAnalyzeErrorsEvent::NAME, $event->getMass()->getUser(), $event->getMass());
    }

    public function onMassMatched(MassMatchedEvent $event)
    {
        $this->notificationManager->notifies(MassMatchedEvent::NAME, $event->getMass()->getUser(), $event->getMass());
    }

    public function onMassPublicTransportSolutionsGathered(MassPublicTransportSolutionsGatheredEvent $event)
    {
        $this->notificationManager->notifies(MassPublicTransportSolutionsGatheredEvent::NAME, $event->getMass()->getUser(), $event->getMass());
    }
}
