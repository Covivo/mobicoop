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

use Symfony\Contracts\EventDispatcher\Event;
use App\Action\Entity\Action;
use App\Action\Entity\Animation;
use App\Action\Exception\ActionException;
use App\Action\Repository\ActionRepository;
use App\Action\Service\DiaryManager;
use App\App\Entity\App;
use App\Communication\Service\NotificationManager;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryAnimation;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Event\SolidaryAnimationPostedEvent;
use App\Solidary\Event\SolidaryContactEmailEvent;
use App\Solidary\Event\SolidaryContactMessageEvent;
use App\Solidary\Event\SolidaryContactSmsEvent;
use App\Solidary\Event\SolidaryCreatedEvent;
use App\Solidary\Event\SolidaryUpdatedEvent;
use App\Solidary\Event\SolidaryUserCreatedEvent;
use App\Solidary\Event\SolidaryUserStructureAcceptedEvent;
use App\Solidary\Event\SolidaryUserStructureRefusedEvent;
use App\Solidary\Event\SolidaryUserUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
use App\User\Entity\User;
use App\User\Event\LoginDelegateEvent;
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
     * Handle events
     *
     * @param string $actionName    Name of the action of this event
     * @param Event $event          Event of the action
     * @return void
     */
    public function handleAction(string $actionName, Event $event=null)
    {
        // Get the action
        $action = $this->actionRepository->findOneBy(['name'=>$actionName]);
        if (empty($action)) {
            throw new ActionException(ActionException::BAD_ACTION);
        }
        switch ($actionName) {
            case SolidaryUserStructureAcceptedEvent::NAME:$this->onSolidaryUserStructureAccepted($action, $event);
                break;
            case SolidaryUserStructureRefusedEvent::NAME:$this->onSolidaryUserStructureRefused($action, $event);
                break;
            case SolidaryUserCreatedEvent::NAME:$this->onSolidaryUserCreated($action, $event);
                break;
            case SolidaryUserUpdatedEvent::NAME:$this->onSolidaryUserUpdated($action, $event);
                break;
            case SolidaryCreatedEvent::NAME:$this->onSolidaryCreated($action, $event);
                break;
            case SolidaryUpdatedEvent::NAME:$this->onSolidaryUpdated($action, $event);
                break;
            case SolidaryContactMessageEvent::NAME:$this->onSolidaryContactMessage($action, $event);
                break;
            case SolidaryContactSmsEvent::NAME:$this->onSolidaryContactSms($action, $event);
                break;
            case SolidaryContactEmailEvent::NAME:$this->onSolidaryContactEmail($action, $event);
                break;
            case SolidaryAnimationPostedEvent::NAME:$this->onSolidaryAnimationPosted($event);
                break;
            case LoginDelegateEvent::NAME:$this->onLoginDelegate($action, $event);
        }
    }

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
    public function treatDiary(Action $action, User $user, User $author, ?string $comment=null, ?Solidary $solidary=null, ?SolidarySolution $solidarySolution=null, ?float $progression=null)
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
     * TO DO : Check if a log registration is required and do it
     *
     * @return void
     */
    public function treatLog()
    {
        // To Do
    }

    private function onSolidaryUserStructureAccepted(Action $action, SolidaryUserStructureAcceptedEvent $event)
    {
        $user = $event->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->treatDiary($action, $user, $admin);
    }

    private function onSolidaryUserStructureRefused(Action $action, SolidaryUserStructureRefusedEvent $event)
    {
        $user = $event->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->treatDiary($action, $user, $admin);
    }

    private function onSolidaryUserCreated(Action $action, SolidaryUserCreatedEvent $event)
    {
        $this->treatDiary($action, $event->getUser(), $event->getAuthor());
    }

    private function onSolidaryUserUpdated(Action $action, SolidaryUserUpdatedEvent $event)
    {
        $user = $event->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->treatDiary($action, $user, $admin);
    }

    private function onSolidaryCreated(Action $action, SolidaryCreatedEvent $event)
    {
        $this->treatDiary($action, $event->getUser(), $event->getAuthor(), null, $event->getSolidary());
    }

    private function onSolidaryUpdated(Action $action, SolidaryUpdatedEvent $event)
    {
        $user = $event->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
        $admin = $this->security->getUser();
        $this->treatDiary($action, $user, $admin, null, $event->getSolidary());
    }

    private function onSolidaryContactMessage(Action $action, SolidaryContactMessageEvent $event)
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
        $this->notificationManager->notifies(SolidaryContactMessageEvent::NAME, $recipient, $event->getSolidaryContact());


        // Store in diary
        $this->treatDiary($action, $user, $admin, null, $event->getSolidaryContact()->getSolidarySolution()->getSolidary());
    }

    private function onSolidaryContactSms(Action $action, SolidaryContactSmsEvent $event)
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
        $this->notificationManager->notifies(SolidaryContactSmsEvent::NAME, $recipient, $event->getSolidaryContact());

        // Store in diary
        $this->treatDiary($action, $user, $admin, null, $event->getSolidaryContact()->getSolidarySolution()->getSolidary());
    }
    
    private function onSolidaryContactEmail(Action $action, SolidaryContactEmailEvent $event)
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
        $this->notificationManager->notifies(SolidaryContactEmailEvent::NAME, $recipient, $event->getSolidaryContact());

        // Store in diary
        $this->treatDiary($action, $user, $admin, null, $event->getSolidaryContact()->getSolidarySolution()->getSolidary());
    }

    private function onSolidaryAnimationPosted(SolidaryAnimationPostedEvent $event)
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

        // if the animation also implicate a transporter/carpooler we add a diary entry for that transporter/carpooler
        if ($solidaryAnimation->getSolidarySolution()) {
            $user = null;
            if ($solidaryAnimation->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()) {
                $user = $solidaryAnimation->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
            } else {
                $user =  $solidaryAnimation->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
            }
            $this->treatDiary(
                $action,
                $user,
                $solidaryAnimation->getAuthor(),
                $solidaryAnimation->getComment(),
                $solidaryAnimation->getSolidary(),
                null,
                $solidaryAnimation->getProgression()
            );
        }
    }

    private function onLoginDelegate(Action $action, LoginDelegateEvent $event)
    {
        $this->treatDiary($action, $event->getUserDelegated(), $event->getUser());
    }

    public function onAction(Action $action)
    {
        // if inLog do something
        var_dump($action->isInLog());
        die;

        //
        // dsipatch un LogEvent
    }
}
