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

use App\Communication\Entity\Email;
use App\Communication\Event\ContactEmailEvent;
use App\Communication\Service\EmailManager;
use App\Communication\Service\NotificationManager;
use App\TranslatorTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribed to posted contact event
 *
 * Class ContactSubscriber
 * @package App\Communication\EventSubscriber
 */
class ContactSubscriber implements EventSubscriberInterface
{
    use TranslatorTrait;

    /**
     * @var NotificationManager
     */
    private $notificationManager;

    /**
     * @var EmailManager
     */
    private $emailManager;
    /**
     * @var string
     */
    private $emailTemplatePath;
    /**
     * @var string
     */
    private $contactEmailAddress;

    public function __construct(NotificationManager $notificationManager, EmailManager $emailManager, string $emailTemplatePath, string $contactEmailAddress)
    {
        $this->notificationManager = $notificationManager;
        $this->emailManager = $emailManager;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->contactEmailAddress = $contactEmailAddress;
    }

    public static function getSubscribedEvents()
    {
        return [
            ContactEmailEvent::NAME => 'onContactSent'
        ];
    }

    /**
     * Executed when a contact message is sent
     *
     * @param ContactEmailEvent $event
     */
    public function onContactSent(ContactEmailEvent $event)
    {
        $contact = $event->getContact();

        $email = new Email();

        $email->setRecipientEmail($this->contactEmailAddress);
        $email->setSenderEmail($contact->getEmail());
        $email->setReturnEmail($contact->getEmail());
        $email->setSenderFirstName($contact->getGivenName());
        $email->setSenderName($contact->getFamilyName());
        $email->setObject("Nouvelle demande de contact");

        $this->emailManager->send($email, $this->emailTemplatePath . 'contact_email_posted', ['contact' => $contact]);
    }
}
