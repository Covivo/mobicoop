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

use App\Communication\Entity\Contact;
use App\Communication\Entity\Email;
use App\Communication\Event\ContactEmailEvent;
use App\Communication\Service\EmailManager;
use App\Communication\Service\NotificationManager;
// use App\TranslatorTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Subscribed to posted contact event.
 *
 * Class ContactSubscriber
 */
class ContactSubscriber implements EventSubscriberInterface
{
    // use TranslatorTrait;
    public const LANG = 'fr';
    private $notificationManager;
    private $emailManager;
    private $emailTemplatePath;
    private $platformName;
    private $translator;
    private $communicationFolder;

    public function __construct(NotificationManager $notificationManager, EmailManager $emailManager, TranslatorInterface $translator, string $emailTemplatePath, string $platformName, string $communicationFolder)
    {
        $this->notificationManager = $notificationManager;
        $this->emailManager = $emailManager;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->platformName = $platformName;
        $this->translator = $translator;
        $this->communicationFolder = $communicationFolder;
    }

    public static function getSubscribedEvents()
    {
        return [
            ContactEmailEvent::NAME => 'onContactSent',
        ];
    }

    /**
     * Executed when a contact message is sent.
     */
    public function onContactSent(ContactEmailEvent $event)
    {
        $lang = self::LANG;

        /** @var Contact $contact */
        $contact = $event->getContact();

        $email = new Email();
        // Recipients
        if (is_array($contact->getContactType()->getTo()) && count($contact->getContactType()->getTo()) > 0) {
            $email->setRecipientEmail($contact->getContactType()->getTo());
        }
        if (is_array($contact->getContactType()->getCc()) && count($contact->getContactType()->getCc()) > 0) {
            $email->setRecipientEmailCc($contact->getContactType()->getCc());
        }
        if (is_array($contact->getContactType()->getBcc()) && count($contact->getContactType()->getBcc()) > 0) {
            $email->setRecipientEmailBcc($contact->getContactType()->getBcc());
        }

        // Object
        $email->setObject('['.$this->platformName.'] '.$this->translator->trans($contact->getContactType()->getObjectCode()));

        // Sender
        $email->setSenderEmail($contact->getEmail());
        $email->setReturnEmail($contact->getEmail());
        $email->setSenderFirstName($contact->getGivenName());
        $email->setSenderName($contact->getFamilyName());

        $this->emailManager->send($email, $this->communicationFolder.$lang.$this->emailTemplatePath.'contact_email_posted', ['contact' => $contact]);
    }
}
