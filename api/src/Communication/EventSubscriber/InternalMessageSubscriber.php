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
 */

namespace App\Communication\EventSubscriber;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\Communication\Event\InternalMessageReceivedEvent;
use App\Communication\Service\NotificationManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InternalMessageSubscriber implements EventSubscriberInterface
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
            InternalMessageReceivedEvent::NAME => 'onInternalMessageReceived',
        ];
    }

    public function onInternalMessageReceived(InternalMessageReceivedEvent $event)
    {
        $this->notificationManager->notifies(InternalMessageReceivedEvent::NAME, $event->getRecipient()->getUser(), $event->getRecipient()->getMessage());

        //  we dispatch the gamification event associated
        $action = $this->actionRepository->findOneBy(['name' => 'communication_internal_message_received']);
        $actionEvent = new ActionEvent($action, $event->getRecipient()->getMessage()->getUser());
        $actionEvent->setMessage($event->getRecipient()->getMessage());
        $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
    }
}
