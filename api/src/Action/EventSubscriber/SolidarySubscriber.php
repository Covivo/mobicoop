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
use App\Solidary\Event\SolidaryAnimationPosted;
use App\Solidary\Event\SolidaryContactEmail;
use App\Solidary\Event\SolidaryContactMessage;
use App\Solidary\Event\SolidaryContactSms;
use App\Solidary\Event\SolidaryCreated;
use App\Solidary\Event\SolidaryUpdated;
use App\Solidary\Event\SolidaryUserCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use App\Solidary\Event\SolidaryUserUpdated;

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

    public static function getSubscribedEvents()
    {
        return [
            SolidaryUserStructureAccepted::NAME => 'onSolidaryUserStructureAccepted',
            SolidaryUserStructureRefused::NAME => 'onSolidaryUserStructureRefused',
            SolidaryUserCreated::NAME => 'onSolidaryUserCreated',
            SolidaryUserUpdated::NAME => 'onSolidaryUserUpdated',
            SolidaryCreated::NAME => 'onSolidaryCreated',
            SolidaryUpdated::NAME => 'onSolidaryUpdated',
            SolidaryContactMessage::NAME => 'onSolidaryContactMessage',
            SolidaryContactEmail::NAME => 'onSolidaryContactEmail',
            SolidaryContactSms::NAME => 'onSolidaryContactSms',
            SolidaryAnimationPosted::NAME => 'onSolidaryAnimationPosted'
        ];
    }

    public function onSolidaryUserStructureAccepted(SolidaryUserStructureAccepted $event)
    {
        $this->actionManager->handleAction(SolidaryUserStructureAccepted::NAME, $event);
    }

    public function onSolidaryUserStructureRefused(SolidaryUserStructureRefused $event)
    {
        $this->actionManager->handleAction(SolidaryUserStructureRefused::NAME, $event);
    }

    public function onSolidaryUserCreated(SolidaryUserCreated $event)
    {
        $this->actionManager->handleAction(SolidaryUserCreated::NAME, $event);
    }

    public function onSolidaryUserUpdated(SolidaryUserUpdated $event)
    {
        $this->actionManager->handleAction(SolidaryUserUpdated::NAME, $event);
    }

    public function onSolidaryCreated(SolidaryCreated $event)
    {
        $this->actionManager->handleAction(SolidaryCreated::NAME, $event);
    }

    public function onSolidaryUpdated(SolidaryUpdated $event)
    {
        $this->actionManager->handleAction(SolidaryUpdated::NAME, $event);
    }

    public function onSolidaryContactMessage(SolidaryContactMessage $event)
    {
        $this->actionManager->handleAction(SolidaryContactMessage::NAME, $event);
    }

    public function onSolidaryContactEmail(SolidaryContactEmail $event)
    {
        $this->actionManager->handleAction(SolidaryContactEmail::NAME, $event);
    }

    public function onSolidaryContactSms(SolidaryContactSms $event)
    {
        $this->actionManager->handleAction(SolidaryContactSms::NAME, $event);
    }

    public function onSolidaryAnimationPosted(SolidaryAnimationPosted $event)
    {
        $this->actionManager->handleAction(SolidaryAnimationPosted::NAME, $event);
    }
}
