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
use App\User\Event\ReviewReceivedEvent;
use App\User\Event\UserDelegateRegisteredEvent;
use App\User\Event\UserDelegateRegisteredPasswordSendEvent;
use App\User\Event\UserGeneratePhoneTokenAskedEvent;
use App\User\Event\UserPasswordChangeAskedEvent;
use App\User\Event\UserPasswordChangedEvent;
use App\User\Event\UserSendValidationEmailEvent;
use App\User\Service\UserManager;
use App\Communication\Service\SmsManager;

class UserSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $userManager;

    public function __construct(NotificationManager $notificationManager, UserManager $userManager)
    {
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserRegisteredEvent::NAME => 'onUserRegistered',
            UserDelegateRegisteredEvent::NAME => 'onUserDelegateRegistered',
            UserDelegateRegisteredPasswordSendEvent::NAME => 'onUserDelegateRegisteredPasswordSend',
            UserUpdatedSelfEvent::NAME => 'onUserUpdatedSelf',
            UserPasswordChangeAskedEvent::NAME => 'onUserPasswordChangeAsked',
            UserPasswordChangedEvent::NAME => 'onUserPasswordChanged',
            UserGeneratePhoneTokenAskedEvent::NAME => 'onUserGeneratePhoneTokenAskedEvent',
            UserDeleteAccountWasDriverEvent::NAME => 'onUserDeleteAccountWasDriverEvent',
            UserDeleteAccountWasPassengerEvent::NAME => 'onUserDeleteAccountWasPassengerEvent',
            ReviewReceivedEvent::NAME => 'onReviewReceivedEvent',
            UserSendValidationEmailEvent::NAME => 'onUserSendValidationEmail'
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $this->notificationManager->notifies(UserRegisteredEvent::NAME, $event->getUser());
    }

    public function onUserDelegateRegistered(UserDelegateRegisteredEvent $event)
    {
        $this->notificationManager->notifies(UserDelegateRegisteredEvent::NAME, $event->getUser());
    }

    public function onUserDelegateRegisteredPasswordSend(UserDelegateRegisteredPasswordSendEvent $event)
    {
        $this->notificationManager->notifies(UserDelegateRegisteredPasswordSendEvent::NAME, $event->getUser());
    }

    public function onUserUpdatedSelf(UserUpdatedSelfEvent $event)
    {
        $this->userManager->updatePaymentProviderUser($event->getUser());
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
        if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
            $this->notificationManager->notifies(UserDeleteAccountWasDriverEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
        } else {
            $this->notificationManager->notifies(UserDeleteAccountWasDriverEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
        }
    }

    public function onUserDeleteAccountWasPassengerEvent(UserDeleteAccountWasPassengerEvent $event)
    {
        if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
            $this->notificationManager->notifies(UserDeleteAccountWasPassengerEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
        } else {
            $this->notificationManager->notifies(UserDeleteAccountWasPassengerEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
        }
    }

    public function onReviewReceivedEvent(ReviewReceivedEvent $event)
    {
        $this->notificationManager->notifies(ReviewReceivedEvent::NAME, $event->getReview()->getReviewed(), $event->getReview());
    }

    public function onUserSendValidationEmail(UserSendValidationEmailEvent $event)
    {
        $this->notificationManager->notifies(UserSendValidationEmailEvent::NAME, $event->getUser());
    }
}
