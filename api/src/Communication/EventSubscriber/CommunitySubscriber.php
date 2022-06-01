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
use App\Community\Event\CommunityNewMembershipRequestEvent;
use App\Community\Event\CommunityNewMemberEvent;
use App\Community\Event\CommunityCreatedEvent;
use App\Community\Event\CommunityMembershipAcceptedEvent;
use App\Community\Event\CommunityMembershipPendingEvent;
use App\Community\Event\CommunityMembershipRefusedEvent;
use App\Action\Event\ActionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Action\Repository\ActionRepository;
use App\Community\Entity\CommunityUser;

class CommunitySubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $eventDispatcher;
    private $actionRepository;

    public function __construct(NotificationManager $notificationManager, EventDispatcherInterface $eventDispatcher, ActionRepository $actionRepository)
    {
        $this->notificationManager = $notificationManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->actionRepository = $actionRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommunityNewMembershipRequestEvent::NAME => 'onCommunityNewMembershipRequest',
            CommunityCreatedEvent::NAME => 'onCommunityCreated',
            CommunityMembershipAcceptedEvent::NAME => 'onCommunityMembershipAccepted',
            CommunityMembershipPendingEvent::NAME => 'onCommunityMembershipPending',
            CommunityMembershipRefusedEvent::NAME => 'onCommunityMembershipRefused',
            CommunityNewMemberEvent::NAME => 'onCommunityMember'

        ];
    }

    /**
     * Executed when an user joined a community with validation
     *
     * @param CommunityNewMembershipRequestEvent $event
     * @return void
     */
    public function onCommunityNewMembershipRequest(CommunityNewMembershipRequestEvent $event): void
    {
        // the recipient is the creator of community
        $communityRecipient = ($event->getCommunityUser()->getCommunity()->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityNewMembershipRequestEvent::NAME, $communityRecipient, $event->getCommunityUser());

        // we also need to notify community's moderators
        $communityUsers = $event->getCommunityUser()->getCommunity()->getCommunityUsers();
        foreach ($communityUsers as $communityUser) {
            if ($communityUser->getStatus() === CommunityUser::STATUS_ACCEPTED_AS_MODERATOR) {
                $communityRecipient = $communityUser->getUser();
                $this->notificationManager->notifies(CommunityNewMembershipRequestEvent::NAME, $communityRecipient, $event->getCommunityUser());
            }
        }
    }

    /**
     * Executed when an user joined a communitywithout validation
     *
     * @param CommunityNewMemberEvent $event
     * @return void
     */
    public function onCommunityMember(CommunityNewMemberEvent $event): void
    {
        // the recipient is the new community member
        $communityRecipient = ($event->getCommunityUser()->getCommunity()->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityNewMemberEvent::NAME, $communityRecipient, $event->getCommunityUser());

        // we also need to notify community's moderators
        $communityUsers = $event->getCommunityUser()->getCommunity()->getCommunityUsers();
        foreach ($communityUsers as $communityUser) {
            if ($communityUser->getStatus() === CommunityUser::STATUS_ACCEPTED_AS_MODERATOR) {
                $communityRecipient = $communityUser->getUser();
                $this->notificationManager->notifies(CommunityNewMemberEvent::NAME, $communityRecipient, $event->getCommunityUser());
            }
        }
    }

    /**
     * Executed when a community is created
     *
     * @param CommunityCreatedEvent $event
     * @return void
     */
    public function onCommunityCreated(CommunityCreatedEvent $event): void
    {
        // the recipient is the new community member
        $communityRecipient = ($event->getCommunity()->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityCreatedEvent::NAME, $communityRecipient, $event->getCommunity());

        $action = $this->actionRepository->findOneBy(['name'=>'community_created']);
        $actionEvent = new ActionEvent($action, $event->getCommunity()->getUser());
        $actionEvent->setCommunity($event->getCommunity());
        $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
    }

    /**
     * Executed when a communityModerator validates a membership
     *
     * @param CommunityMembershipAcceptedEvent $event
     * @return void
     */
    public function onCommunityMembershipAccepted(CommunityMembershipAcceptedEvent $event): void
    {
        // the recipient is the creator of community
        $communityRecipient = ($event->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityMembershipAcceptedEvent::NAME, $communityRecipient, $event->getCommunity());
    }

    /**
     * Executed when an user joined a community with validation
     *
     * @param CommunityMembershipPendingEvent $event
     * @return void
     */
    public function onCommunityMembershipPending(CommunityMembershipPendingEvent $event): void
    {
        // the recipient is the creator of community
        $communityRecipient = ($event->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityMembershipPendingEvent::NAME, $communityRecipient, $event->getCommunity());
    }

    /**
     * Executed when a communityModerator refuses a membership
     *
     * @param CommunityMembershipRefusedEvent $event
     * @return void
     */
    public function onCommunityMembershipRefused(CommunityMembershipRefusedEvent $event): void
    {
        // the recipient is the creator of community
        $communityRecipient = ($event->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityMembershipRefusedEvent::NAME, $communityRecipient, $event->getCommunity());
    }
}
