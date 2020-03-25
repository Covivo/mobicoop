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
 **************************/

namespace App\Action\EventSubscriber;

use App\Action\Service\ActionManager;
use App\Solidary\Event\SolidaryCreated;
use App\Solidary\Event\SolidaryUpdated;
use App\Solidary\Event\SolidaryUserCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use App\Solidary\Event\SolidaryUserUpdated;

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
        ];
    }

    public function onSolidaryUserStructureAccepted(SolidaryUserStructureAccepted $event)
    {
        $this->actionManager->handleEvent(SolidaryUserStructureAccepted::NAME, $event);
    }

    public function onSolidaryUserStructureRefused(SolidaryUserStructureRefused $event)
    {
        $this->actionManager->handleEvent(SolidaryUserStructureRefused::NAME, $event);
    }

    public function onSolidaryUserCreated(SolidaryUserCreated $event)
    {
        $this->actionManager->handleEvent(SolidaryUserCreated::NAME, $event);
    }

    public function onSolidaryUserUpdated(SolidaryUserUpdated $event)
    {
        $this->actionManager->handleEvent(SolidaryUserUpdated::NAME, $event);
    }

    public function onSolidaryCreated(SolidaryCreated $event)
    {
        $this->actionManager->handleEvent(SolidaryCreated::NAME, $event);
    }

    public function onSolidaryUpdated(SolidaryUpdated $event)
    {
        $this->actionManager->handleEvent(SolidaryUpdated::NAME, $event);
    }
}
