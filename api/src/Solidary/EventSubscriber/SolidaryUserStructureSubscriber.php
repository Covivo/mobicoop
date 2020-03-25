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

namespace App\Solidary\EventSubscriber;

use App\Action\Repository\ActionRepository;
use App\Action\Service\DiaryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Communication\Service\NotificationManager;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use App\Solidary\Service\SolidaryUserStructureManager;
use Symfony\Component\Security\Core\Security;

class SolidaryUserStructureSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $solidaryUserStructureManager;
    private $diaryManager;
    private $actionRepository;
    private $security;

    public function __construct(NotificationManager $notificationManager, SolidaryUserStructureManager $solidaryUserStructureManager, DiaryManager $diaryManager, ActionRepository $actionRepository, Security $security)
    {
        $this->notificationManager = $notificationManager;
        $this->solidaryUserStructureManager = $solidaryUserStructureManager;
        $this->diaryManager = $diaryManager;
        $this->actionRepository = $actionRepository;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            SolidaryUserStructureAccepted::NAME => 'onSolidaryUserStructureAccepted',
            SolidaryUserStructureRefused::NAME => 'onSolidaryUserStructureRefused',
        ];
    }

    public function onSolidaryUserStructureAccepted(SolidaryUserStructureAccepted $event)
    {
        $action = $this->actionRepository->findOneBy(['name'=>SolidaryUserStructureAccepted::NAME]);
        $user = $event->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }

    public function onSolidaryUserStructureRefused(SolidaryUserStructureRefused $event)
    {
        $action = $this->actionRepository->findOneBy(['name'=>SolidaryUserStructureRefused::NAME]);
        $user = $event->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }
}
