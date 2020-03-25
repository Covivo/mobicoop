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

namespace App\Solidary\Service;

use App\Action\Entity\Action;
use App\Action\Repository\ActionRepository;
use App\Action\Service\DiaryManager;
use App\App\Entity\App;
use App\Communication\Service\NotificationManager;
use App\Solidary\Event\SolidaryUserCreated;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use App\Solidary\Event\SolidaryUserUpdated;
use App\Solidary\Exception\SolidaryException;
use Symfony\Component\Security\Core\Security;

/**
 * Solidary event manager
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryEventManager
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
    
    /**
     * Handle a Solidary Event
     *
     * @param string $actionName    Name of the action of this event
     * @param Object $object        Event of the action
     * @return void
     */
    public function handleEvent(string $actionName, Object $object)
    {
        // Get the action
        $action = $this->actionRepository->findOneBy(['name'=>$actionName]);
        if (empty($action)) {
            throw new SolidaryException(SolidaryException::BAD_SOLIDARY_ACTION);
        }
        switch ($actionName) {
            case SolidaryUserStructureAccepted::NAME:$this->onSolidaryUserStructureAccepted($action, $object);
                break;
            case SolidaryUserStructureRefused::NAME:$this->onSolidaryUserStructureRefused($action, $object);
                break;
            case SolidaryUserCreated::NAME:$this->onSolidaryUserCreated($action, $object);
                break;
            case SolidaryUserUpdated::NAME:$this->onSolidaryUserUpdated($action, $object);
                break;
            default:
        }
    }

    private function onSolidaryUserStructureAccepted(Action $action, SolidaryUserStructureAccepted $event)
    {
        $user = $event->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }

    private function onSolidaryUserStructureRefused(Action $action, SolidaryUserStructureRefused $event)
    {
        $user = $event->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }

    private function onSolidaryUserCreated(Action $action, SolidaryUserCreated $event)
    {
        $user = $event->getUser();
        $admin = $this->security->getUser();
        // If it's an App, it means that this User registered himself from the front
        if ($admin instanceof App) {
            $admin = $user;
        }
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }

    private function onSolidaryUserUpdated(Action $action, SolidaryUserUpdated $event)
    {
        $user = $event->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }
}
