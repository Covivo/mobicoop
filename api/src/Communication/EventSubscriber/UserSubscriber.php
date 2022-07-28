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

use App\Communication\Service\NotificationManager;
use App\DataProvider\Entity\RezopouceProvider;
use App\User\Admin\Service\UserManager as AdminUserManager;
use App\User\Entity\IdentityProof;
use App\User\Event\IdentityProofModeratedEvent;
use App\User\Event\IdentityProofValidationReminderEvent;
use App\User\Event\IncitateToPublishFirstAdEvent;
use App\User\Event\NewlyRegisteredUserEvent;
use App\User\Event\ReviewReceivedEvent;
use App\User\Event\UserDelegateRegisteredEvent;
use App\User\Event\UserDelegateRegisteredPasswordSendEvent;
use App\User\Event\UserDeleteAccountWasDriverEvent;
use App\User\Event\UserDeleteAccountWasPassengerEvent;
use App\User\Event\UserGeneratePhoneTokenAskedEvent;
use App\User\Event\UserPasswordChangeAskedEvent;
use App\User\Event\UserPasswordChangedEvent;
use App\User\Event\UserRegisteredEvent;
use App\User\Event\UserSendValidationEmailEvent;
use App\User\Event\UserUpdatedSelfEvent;
use App\User\Service\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $userManager;
    private $notificationSsoRegistration;
    private $rzpUri;
    private $rzpLogin;
    private $rzpPassword;

    public function __construct(
        NotificationManager $notificationManager,
        UserManager $userManager,
        AdminUserManager $adminUserManager,
        bool $notificationSsoRegistration,
        string $rzpUri,
        string $rzpLogin,
        string $rzpPassword
    ) {
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
        $this->adminUserManager = $adminUserManager;
        $this->notificationSsoRegistration = $notificationSsoRegistration;
        $this->rzpUri = $rzpUri;
        $this->rzpLogin = $rzpLogin;
        $this->rzpPassword = $rzpPassword;
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
            UserSendValidationEmailEvent::NAME => 'onUserSendValidationEmail',
            IdentityProofModeratedEvent::NAME => 'onIdentityProofModerated',
            IdentityProofValidationReminderEvent::NAME => 'onIdentityProofValidationReminder',
            IncitateToPublishFirstAdEvent::NAME => 'onIncitateToPublishFirstAd',
            NewlyRegisteredUserEvent::NAME => 'onNewlyRegisteredUser',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        if (!is_null($event->getUser()->getSsoId()) && '' !== $event->getUser()->getSsoId() && !$this->notificationSsoRegistration) {
            return;
        }
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

    public function onIdentityProofModerated(IdentityProofModeratedEvent $event)
    {
        if (IdentityProof::STATUS_ACCEPTED == $event->getIdentityProof()->getStatus()) {
            $rzpProvider = new RezopouceProvider($this->rzpUri, $this->rzpLogin, $this->rzpPassword);
            if ($rzpProvider->sendValidationEmail($event->getIdentityProof()->getUser())) {
                $this->adminUserManager->patchUser($event->getIdentityProof()->getUser(), ['rezoKit' => true, 'cardLetter' => true]);
            }
        }
        if (IdentityProof::STATUS_REFUSED == $event->getIdentityProof()->getStatus()) {
            $this->notificationManager->notifies(IdentityProofModeratedEvent::NAME_REJECTED, $event->getIdentityProof()->getUser());
        }
    }

    public function onIdentityProofValidationReminder(IdentityProofValidationReminderEvent $event)
    {
        $this->notificationManager->notifies(IdentityProofValidationReminderEvent::NAME, $event->getIdentityProof()->getUser());
    }

    public function onIncitateToPublishFirstAd(IncitateToPublishFirstAdEvent $event)
    {
        $this->notificationManager->notifies(IncitateToPublishFirstAdEvent::NAME, $event->getUser());
    }

    public function onNewlyRegisteredUser(NewlyRegisteredUserEvent $event)
    {
        $this->notificationManager->notifies(NewlyRegisteredUserEvent::NAME, $event->getUser());
    }
}
