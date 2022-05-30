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

namespace App\Action\EventSubscriber;

use App\Action\Service\ActionManager;
use App\Solidary\Event\SolidaryAnimationPostedEvent;
use App\Solidary\Event\SolidaryContactEmailEvent;
use App\Solidary\Event\SolidaryContactMessageEvent;
use App\Solidary\Event\SolidaryContactSmsEvent;
use App\Solidary\Event\SolidaryCreatedEvent;
use App\Solidary\Event\SolidaryUpdatedEvent;
use App\Solidary\Event\SolidaryUserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Solidary\Event\SolidaryUserStructureAcceptedEvent;
use App\Solidary\Event\SolidaryUserStructureRefusedEvent;
use App\Solidary\Event\SolidaryUserUpdatedEvent;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidarySubscriber implements EventSubscriberInterface
{
    private $actionManager;

    public function __construct(ActionManager $actionManager)
    {
        $this->actionManager = $actionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SolidaryUserStructureAcceptedEvent::NAME => 'onSolidaryUserStructureAccepted',
            SolidaryUserStructureRefusedEvent::NAME => 'onSolidaryUserStructureRefused',
            SolidaryUserCreatedEvent::NAME => 'onSolidaryUserCreated',
            SolidaryUserUpdatedEvent::NAME => 'onSolidaryUserUpdated',
            SolidaryCreatedEvent::NAME => 'onSolidaryCreated',
            SolidaryUpdatedEvent::NAME => 'onSolidaryUpdated',
            SolidaryContactMessageEvent::NAME => 'onSolidaryContactMessage',
            SolidaryContactEmailEvent::NAME => 'onSolidaryContactEmail',
            SolidaryContactSmsEvent::NAME => 'onSolidaryContactSms',
            SolidaryAnimationPostedEvent::NAME => 'onSolidaryAnimationPosted'
        ];
    }

    public function onSolidaryUserStructureAccepted(SolidaryUserStructureAcceptedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryUserStructureAcceptedEvent::NAME, $event);
    }

    public function onSolidaryUserStructureRefused(SolidaryUserStructureRefusedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryUserStructureRefusedEvent::NAME, $event);
    }

    public function onSolidaryUserCreated(SolidaryUserCreatedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryUserCreatedEvent::NAME, $event);
    }

    public function onSolidaryUserUpdated(SolidaryUserUpdatedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryUserUpdatedEvent::NAME, $event);
    }

    public function onSolidaryCreated(SolidaryCreatedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryCreatedEvent::NAME, $event);
    }

    public function onSolidaryUpdated(SolidaryUpdatedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryUpdatedEvent::NAME, $event);
    }

    public function onSolidaryContactMessage(SolidaryContactMessageEvent $event)
    {
        $this->actionManager->handleAction(SolidaryContactMessageEvent::NAME, $event);
    }

    public function onSolidaryContactEmail(SolidaryContactEmailEvent $event)
    {
        $this->actionManager->handleAction(SolidaryContactEmailEvent::NAME, $event);
    }

    public function onSolidaryContactSms(SolidaryContactSmsEvent $event)
    {
        $this->actionManager->handleAction(SolidaryContactSmsEvent::NAME, $event);
    }

    public function onSolidaryAnimationPosted(SolidaryAnimationPostedEvent $event)
    {
        $this->actionManager->handleAction(SolidaryAnimationPostedEvent::NAME, $event);
    }
}
