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

namespace App\Action\Service;

use App\Action\Entity\Action;
use App\Action\Entity\Animation;
use App\Action\Repository\ActionRepository;
use App\Action\Service\DiaryManager;
use App\App\Entity\App;
use App\Communication\Service\NotificationManager;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryAnimation;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Event\SolidaryAnimationPosted;
use App\Solidary\Event\SolidaryContactEmail;
use App\Solidary\Event\SolidaryContactMessage;
use App\Solidary\Event\SolidaryContactSms;
use App\Solidary\Event\SolidaryCreated;
use App\Solidary\Event\SolidaryUpdated;
use App\Solidary\Event\SolidaryUserCreated;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use App\Solidary\Event\SolidaryUserUpdated;
use App\Solidary\Exception\SolidaryException;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * Action Manager
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ActionManager
{
    private $notificationManager;
    private $diaryManager;
    private $actionRepository;
    private $security;

    public function __construct(NotificationManager $notificationManager, DiaryManager $diaryManager, ActionRepository $actionRepository, Security $security)
    {
        $this->notificationManager = $notificationManager;
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
    public function handleAction(string $actionName, Object $object=null)
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
            case SolidaryCreated::NAME:$this->onSolidaryCreated($action, $object);
                break;
            case SolidaryUpdated::NAME:$this->onSolidaryUpdated($action, $object);
                break;
            case SolidaryContactMessage::NAME:$this->onSolidaryContactMessage($action, $object);
                break;
            case SolidaryContactSms::NAME:$this->onSolidaryContactSms($action, $object);
                break;
            case SolidaryContactEmail::NAME:$this->onSolidaryContactEmail($action, $object);
                break;
            case SolidaryAnimationPosted::NAME:$this->onSolidaryAnimationPosted($object);
                break;
            default:
                // For all the manual action with basic behaviour
                // $this->treatAction($action, $object);
                break;
        }
    }

    // private function treatAction(Action $action, Animation $animation)
    // {
    //     if ($action->isInLog()) {
    //         // To do
    //     }
    //     if ($action->isInDiary()) {
    //         $this->diaryManager->addDiaryEntry(
    //             $action,
    //             $animation->getUser(),
    //             $animation->getAuthor(),
    //             $animation->getComment(),
    //             $animation->getSolidary(),
    //             $animation->getSolidarySolution(),
    //             $animation->getProgression()
    //         );
    //     }
    // }

    /**
     * Check if a diary registration is required and do it
     *
     * @param Action $action
     * @param User $user
     * @param User $author
     * @param string $comment
     * @param Solidary $solidary
     * @param SolidarySolution $solidarySolution
     * @param float $progression
     * @return void
     */
    public function treatDiary(Action $action, User $user, User $author, ?string $comment, ?Solidary $solidary=null, ?SolidarySolution $solidarySolution=null, ?float $progression=null)
    {
        if ($action->isInDiary()) {
            $this->diaryManager->addDiaryEntry(
                $action,
                $user,
                $author,
                $comment,
                $solidary,
                $solidarySolution,
                $progression
            );
        }
    }

    /**
     * Check if a log registration is required and do it (WIP)
     *
     * @return void
     */
    public function treatLog()
    {
        // To Do
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
        $this->diaryManager->addDiaryEntry($action, $event->getUser(), $event->getAuthor());
    }

    private function onSolidaryUserUpdated(Action $action, SolidaryUserUpdated $event)
    {
        $user = $event->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin);
    }

    private function onSolidaryCreated(Action $action, SolidaryCreated $event)
    {
        // To do : The solidary is not persisted yet so we can't pass it to addDiaryEntrey... But it would be cool :)
        $this->diaryManager->addDiaryEntry($action, $event->getUser(), $event->getAuthor());
    }

    private function onSolidaryUpdated(Action $action, SolidaryUpdated $event)
    {
        $user = $event->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->diaryManager->addDiaryEntry($action, $user, $admin, null, $event->getSolidary());
    }

    private function onSolidaryContactMessage(Action $action, SolidaryContactMessage $event)
    {
        $solidaryContact = $event->getSolidaryContact();
        $user = $solidaryContact->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        
        
        if (!is_null($solidaryContact->getSolidarySolution()->getSolidaryMatching()->getMatching())) {
            $recipient = $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
        } else {
            $recipient = $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
        }
        $admin = $this->security->getUser();

        // Trigger the message by notifies
        $this->notificationManager->notifies(SolidaryContactMessage::NAME, $recipient, $event->getSolidaryContact());


        // Store in diary
        $this->diaryManager->addDiaryEntry($action, $user, $admin, null, $event->getSolidaryContact()->getSolidarySolution()->getSolidary());
    }

    private function onSolidaryContactSms(Action $action, SolidaryContactSms $event)
    {
        $solidaryContact = $event->getSolidaryContact();
        $user = $solidaryContact->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        
        
        if (!is_null($solidaryContact->getSolidarySolution()->getSolidaryMatching()->getMatching())) {
            $recipient = $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
        } else {
            $recipient = $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
        }
        $admin = $this->security->getUser();

        // Trigger the sms by notifies (need to add lines in table notification)
        $this->notificationManager->notifies(SolidaryContactSms::NAME, $recipient, $event->getSolidaryContact());

        // Store in diary
        $this->diaryManager->addDiaryEntry($action, $user, $admin, null, $event->getSolidaryContact()->getSolidarySolution()->getSolidary());
    }
    
    private function onSolidaryContactEmail(Action $action, SolidaryContactEmail $event)
    {
        $solidaryContact = $event->getSolidaryContact();
        $user = $solidaryContact->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        
        
        if (!is_null($solidaryContact->getSolidarySolution()->getSolidaryMatching()->getMatching())) {
            $recipient = $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
        } else {
            $recipient = $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
        }
        $admin = $this->security->getUser();

        // Trigger the email by notifies
        $this->notificationManager->notifies(SolidaryContactEmail::NAME, $recipient, $event->getSolidaryContact());

        // Store in diary
        $this->diaryManager->addDiaryEntry($action, $user, $admin, null, $event->getSolidaryContact()->getSolidarySolution()->getSolidary());
    }

    private function onSolidaryAnimationPosted(SolidaryAnimationPosted $event)
    {
        $solidaryAnimation = $event->getSolidaryAnimation();

        // We get the action of this SolidaryAnimation
        $action = $this->actionRepository->findOneBy(['name'=>$solidaryAnimation->getActionName()]);
        if (empty($action)) {
            throw new SolidaryException(SolidaryException::BAD_SOLIDARY_ACTION);
        }

        $this->treatDiary(
            $action,
            $solidaryAnimation->getUser(),
            $solidaryAnimation->getAuthor(),
            $solidaryAnimation->getComment(),
            $solidaryAnimation->getSolidary(),
            $solidaryAnimation->getSolidarySolution(),
            $solidaryAnimation->getProgression()
        );
    }
}
