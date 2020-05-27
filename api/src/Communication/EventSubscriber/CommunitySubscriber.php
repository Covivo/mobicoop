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

namespace App\Communication\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Communication\Service\NotificationManager;
use App\Community\Event\CommunityNewMembershipRequestEvent;


class CommunitySubscriber implements EventSubscriberInterface
{
    private $notificationManager;


    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CommunityNewMembershipRequestEvent::NAME => 'onCommunityNewMembershipRequest'
        ];
    }


    /**
     * Executed when an user joined a community
     *
     * @param CommunityNewMembershipRequestEvent $event
     * @return void
     */
    public function onCommunityNewMembershipRequest(CommunityNewMembershipRequestEvent $event)
    {
        // the recipient is the creator of community
        $communityRecipient = ($event->getCommunity()->getUser());

        // we must notify the creator of the community
        $this->notificationManager->notifies(CommunityNewMembershipRequestEvent::NAME, $communityRecipient, $event->getCommunity());
    }
}






