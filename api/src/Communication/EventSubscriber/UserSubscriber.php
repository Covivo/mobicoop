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

use App\User\Event\UserDeleteAccountWasDriverEvent;
use App\User\Event\UserDeleteAccountWasPassengerEvent;
use App\User\Event\UserRegisteredEvent;
use App\User\Event\UserUpdatedSelfEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Communication\Service\NotificationManager;
use App\User\Event\UserGeneratePhoneTokenAskedEvent;
use App\User\Event\UserPasswordChangeAskedEvent;
use App\User\Event\UserPasswordChangedEvent;

class UserSubscriber implements EventSubscriberInterface
{
    private $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserRegisteredEvent::NAME => 'onUserRegistered',
            UserUpdatedSelfEvent::NAME => 'onUserUpdatedSelf',
            UserPasswordChangeAskedEvent::NAME => 'onUserPasswordChangeAsked',
            UserPasswordChangedEvent::NAME => 'onUserPasswordChanged',
            UserGeneratePhoneTokenAskedEvent::NAME => 'onUserGeneratePhoneTokenAskedEvent',
            UserDeleteAccountWasDriverEvent::NAME => 'onUserDeleteAccountWasDriverEvent',
            UserDeleteAccountWasPassengerEvent::NAME => 'onUserDeleteAccountWasPassengerEvent'
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $this->notificationManager->notifies(UserRegisteredEvent::NAME, $event->getUser());
    }

    public function onUserUpdatedSelf(UserUpdatedSelfEvent $event)
    {
        $this->notificationManager->notifies(UserUpdatedSelfEvent::NAME, $event->getUser());
    }

    public function onUserPasswordChangeAsked(UserPasswordChangeAskedEvent $event)
    {
        $this->notificationManager->notifies(UserPasswordChangeAskedEvent::NAME, $event->getUser());
    }

    public function onUserPasswordChanged(UserPasswordChangedEvent $event)
    {
        $this->notificationManager->notifies(UserPasswordChangedEvent::NAME, $event->getUser());
    }

    public function onUserGeneratePhoneTokenAskedEvent(UserGeneratePhoneTokenAskedEvent $event)
    {
        $this->notificationManager->notifies(UserGeneratePhoneTokenAskedEvent::NAME, $event->getUser());
    }

    public function onUserDeleteAccountWasDriverEvent(UserDeleteAccountWasDriverEvent $event)
    {
        $this->notificationManager->notifies(UserDeleteAccountWasDriverEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
    }

    public function onUserDeleteAccountWasPassengerEvent(UserDeleteAccountWasPassengerEvent $event)
    {
        $this->notificationManager->notifies(UserDeleteAccountWasPassengerEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
    }
}
