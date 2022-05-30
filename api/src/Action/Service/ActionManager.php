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

namespace App\Action\Service;

use App\Action\Entity\Action;
use App\Action\Entity\Animation;
use App\Action\Entity\Log;
use App\Action\Event\ActionEvent;
use App\Action\Event\LogEvent;
use App\Action\Exception\ActionException;
use App\Action\Repository\ActionRepository;
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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Action Manager.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ActionManager
{
    private $notificationManager;
    private $diaryManager;
    private $actionRepository;
    private $security;
    private $entityManager;
    private $eventDispatcher;

    public function __construct(
        NotificationManager $notificationManager,
        DiaryManager $diaryManager,
        ActionRepository $actionRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->notificationManager = $notificationManager;
        $this->diaryManager = $diaryManager;
        $this->actionRepository = $actionRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get an Action by its name.
     *
     * @param string $name The action's name
     */
    public function getActionByName(string $name): Action
    {
        $action = $this->actionRepository->findOneBy(['name' => $name]);
        if (is_null($action)) {
            throw new ActionException(ActionException::BAD_ACTION);
        }

        return $action;
    }

    /**
     * Handle events.
     *
     * @param string $actionName Name of the action of this event
     * @param Event  $event      Event of the action
     */
    public function handleAction(string $actionName, Event $event = null)
    {
        // Get the action
        $action = $this->actionRepository->findOneBy(['name' => $actionName]);
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

                break;
        }
    }

    public function onAction(ActionEvent $actionEvent)
    {
        // if Action needs to be logged
        if ($actionEvent->getAction()->isInLog()) {
            $log = new Log();
            $log->setAction($actionEvent->getAction());

            if (!is_null($actionEvent->getUser())) {
                $log->setUser($actionEvent->getUser());
            }
            if (!is_null($actionEvent->getUserDelegate())) {
                $log->setUserDelegate($actionEvent->getUserDelegate());
            }
            if (!is_null($actionEvent->getUserRelated())) {
                $log->setUserRelated($actionEvent->getUserRelated());
            }
            if (!is_null($actionEvent->getProposal())) {
                $log->setProposal($actionEvent->getProposal());
            }
            if (!is_null($actionEvent->getMatching())) {
                $log->setMatching($actionEvent->getMatching());
            }
            if (!is_null($actionEvent->getAsk())) {
                $log->setAsk($actionEvent->getAsk());
            }
            if (!is_null($actionEvent->getArticle())) {
                $log->setArticle($actionEvent->getArticle());
            }
            if (!is_null($actionEvent->getEvent())) {
                $log->setEvent($actionEvent->getEvent());
            }
            if (!is_null($actionEvent->getCommunity())) {
                $log->setCommunity($actionEvent->getCommunity());
            }
            if (!is_null($actionEvent->getSolidary())) {
                $log->setSolidary($actionEvent->getSolidary());
            }
            if (!is_null($actionEvent->getTerritory())) {
                $log->setTerritory($actionEvent->getTerritory());
            }
            if (!is_null($actionEvent->getCar())) {
                $log->setCar($actionEvent->getCar());
            }
            if (!is_null($actionEvent->getMessage())) {
                $log->setMessage($actionEvent->getMessage());
            }
            if (!is_null($actionEvent->getCampaign())) {
                $log->setCampaign($actionEvent->getCampaign());
            }
            if (!is_null($actionEvent->getCarpoolPayment())) {
                $log->setCarpoolPayment($actionEvent->getCarpoolPayment());
            }
            if (!is_null($actionEvent->getCarpoolItem())) {
                $log->setCarpoolItem($actionEvent->getCarpoolItem());
            }

            $this->entityManager->persist($log);
            $this->entityManager->flush();

            // Dispatch a LogEvent
            $event = new LogEvent($log);
            $this->eventDispatcher->dispatch($event, LogEvent::NAME);
        }
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
        $action = $this->actionRepository->findOneBy(['name' => $solidaryAnimation->getActionName()]);
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
                $user = $solidaryAnimation->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
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

    /**
     * Check if a diary registration is required and do it.
     *
     * @param string           $comment
     * @param Solidary         $solidary
     * @param SolidarySolution $solidarySolution
     */
    private function treatDiary(Action $action, User $user, User $author, ?string $comment = null, ?Solidary $solidary = null, ?SolidarySolution $solidarySolution = null, float $progression = 0)
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
}
