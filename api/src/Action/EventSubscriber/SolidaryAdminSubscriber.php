<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

use App\Action\Exception\ActionException;
use App\Action\Repository\ActionRepository;
use App\Action\Service\DiaryManager;
use App\Solidary\Admin\Event\SolidaryCreatedEvent;
use App\Solidary\Admin\Event\SolidaryDeeplyUpdated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for Solidary events in admin context
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryAdminSubscriber implements EventSubscriberInterface
{
    private $diaryManager;
    private $actionRepository;

    public function __construct(DiaryManager $diaryManager, ActionRepository $actionRepository)
    {
        $this->diaryManager = $diaryManager;
        $this->actionRepository = $actionRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            SolidaryCreatedEvent::NAME => 'onSolidaryCreated',
            SolidaryDeeplyUpdated::NAME => 'onSolidaryDeeplyUpdated'
        ];
    }

    public function onSolidaryCreated(SolidaryCreatedEvent $event)
    {
        if (!$action = $this->actionRepository->findOneBy(['name'=>SolidaryCreatedEvent::ACTION])) {
            throw new ActionException(ActionException::BAD_ACTION);
        }
        $this->diaryManager->addDiaryEntry(
            $action,
            $event->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser(),
            $event->getPoster(),
            null,
            $event->getSolidary(),
            null,
            0
        );
    }

    public function onSolidaryDeeplyUpdated(SolidaryDeeplyUpdated $event)
    {
        if (!$action = $this->actionRepository->findOneBy(['name'=>SolidaryDeeplyUpdated::NAME])) {
            throw new ActionException(ActionException::BAD_ACTION);
        }
        $this->diaryManager->addDiaryEntry(
            $action,
            $event->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser(),
            $event->getPoster(),
            null,
            $event->getSolidary(),
            null,
            null
        );
    }
}
