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

namespace App\Communication\EventSubscriber;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\Communication\Service\NotificationManager;
use App\Community\Entity\CommunityUser;
use App\Community\Event\CommunityCreatedEvent;
use App\Community\Event\CommunityMembershipAcceptedEvent;
use App\Community\Event\CommunityMembershipPendingEvent;
use App\Community\Event\CommunityMembershipRefusedEvent;
use App\Community\Event\CommunityNewMemberEvent;
use App\Community\Event\CommunityNewMembershipRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    public static function getSubscribedEvents()
    {
        return [
            CommunityNewMembershipRequestEvent::NAME => 'onCommunityNewMembershipRequest',
            CommunityCreatedEvent::NAME => 'onCommunityCreated',
            CommunityMembershipAcceptedEvent::NAME => 'onCommunityMembershipAccepted',
            CommunityMembershipPendingEvent::NAME => 'onCommunityMembershipPending',
            CommunityMembershipRefusedEvent::NAME => 'onCommunityMembershipRefused',
            CommunityNewMemberEvent::NAME => 'onCommunityMember',
        ];
    }

    /**
     * Executed when an user joined a community with validation.
     */
    public function onCommunityNewMembershipRequest(CommunityNewMembershipRequestEvent $event)
    {
        // the recipient is the creator of community
        $communityCreator = $event->getCommunityUser()->getCommunity()->getUser();

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityNewMembershipRequestEvent::NAME, $communityCreator, $event->getCommunityUser());

        // we also need to notify community's moderators (exclude the creator)
        $communityUsers = $event->getCommunityUser()->getCommunity()->getCommunityUsers();
        foreach ($communityUsers as $communityUser) {
            if (CommunityUser::STATUS_ACCEPTED_AS_MODERATOR === $communityUser->getStatus()
                    && $communityCreator->getId() !== $communityUser->getUser()->getId()) {
                $communityRecipient = $communityUser->getUser();
                $this->notificationManager->notifies(CommunityNewMembershipRequestEvent::NAME, $communityRecipient, $event->getCommunityUser());
            }
        }
    }

    /**
     * Executed when an user joined a communitywithout validation.
     */
    public function onCommunityMember(CommunityNewMemberEvent $event)
    {
        // the recipient is the new community member
        $communityCreator = $event->getCommunityUser()->getCommunity()->getUser();

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityNewMemberEvent::NAME, $communityCreator, $event->getCommunityUser());

        // we also need to notify community's moderators
        $communityUsers = $event->getCommunityUser()->getCommunity()->getCommunityUsers();
        foreach ($communityUsers as $communityUser) {
            if (CommunityUser::STATUS_ACCEPTED_AS_MODERATOR === $communityUser->getStatus()
                    && $communityCreator->getId() !== $communityUser->getUser()->getId()) {
                $communityRecipient = $communityUser->getUser();
                $this->notificationManager->notifies(CommunityNewMemberEvent::NAME, $communityRecipient, $event->getCommunityUser());
            }
        }
    }

    /**
     * Executed when a community is created.
     */
    public function onCommunityCreated(CommunityCreatedEvent $event)
    {
        // the recipient is the new community member
        $communityRecipient = $event->getCommunity()->getUser();

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityCreatedEvent::NAME, $communityRecipient, $event->getCommunity());

        $action = $this->actionRepository->findOneBy(['name' => 'community_created']);
        $actionEvent = new ActionEvent($action, $event->getCommunity()->getUser());
        $actionEvent->setCommunity($event->getCommunity());
        $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
    }

    /**
     * Executed when a communityModerator validates a membership.
     */
    public function onCommunityMembershipAccepted(CommunityMembershipAcceptedEvent $event)
    {
        // the recipient is the creator of community
        $communityRecipient = $event->getUser();

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityMembershipAcceptedEvent::NAME, $communityRecipient, $event->getCommunity());
    }

    /**
     * Executed when an user joined a community with validation.
     */
    public function onCommunityMembershipPending(CommunityMembershipPendingEvent $event)
    {
        // the recipient is the creator of community
        $communityRecipient = $event->getUser();

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityMembershipPendingEvent::NAME, $communityRecipient, $event->getCommunity());
    }

    /**
     * Executed when a communityModerator refuses a membership.
     */
    public function onCommunityMembershipRefused(CommunityMembershipRefusedEvent $event)
    {
        // the recipient is the creator of community
        $communityRecipient = $event->getUser();

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityMembershipRefusedEvent::NAME, $communityRecipient, $event->getCommunity());
    }
}
