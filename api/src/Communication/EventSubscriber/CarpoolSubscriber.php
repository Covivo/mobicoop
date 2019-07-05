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
use App\Carpool\Event\AskPostedEvent;
use App\Communication\Entity\Recipient;

class CarpoolSubscriber implements EventSubscriberInterface
{
    private $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            AskPostedEvent::NAME => 'onAskPosted'
        ];
    }

    public function onAskPosted(AskPostedEvent $event)
    {
        // we must notify the recipient of the ask
        if ($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId()) {
            // the recipient is the driver, the message lies in the first ask history
            $this->notificationManager->notifies('ask_posted', $event->getAsk()->getMatching()->getProposalOffer()->getUser(), $event->getAsk()->getAskHistories()[0]);
        } else {
            // the recipient is the passenger, the message lies in the first ask history
            $this->notificationManager->notifies('ask_posted', $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $event->getAsk()->getAskHistories()[0]);
        }
    }
}
