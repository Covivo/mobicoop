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
use App\Communication\Entity\Contact;
use App\Communication\Ressource\ContactType;
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

    private $notificationManager;
    private $emailManager;
    private $emailTemplatePath;
    private $platformName;

    public function __construct(NotificationManager $notificationManager, EmailManager $emailManager, string $emailTemplatePath, string $platformName)
    {
        $this->notificationManager = $notificationManager;
        $this->emailManager = $emailManager;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->platformName = $platformName;
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
    // public function onContactSent(ContactEmailEvent $event)
    // {
    //     $contact = $event->getContact();

    //     $email = new Email();

    //     // we check if we have also CC and BCC contact emails if yes we set them
    //     $contactRecipients=$this->contactEmailAddress;
    //     $contactEmail = null;
    //     $contactEmailBcc = [];
    //     $contactEmailCc = [];
    //     foreach ($contactRecipients as $key => $value) {
    //         if ($key == Contact::SEND_TO) {
    //             $contactEmail = $value;
    //         } elseif ($key == Contact::SEND_CC) {
    //             $contactEmailCc = $value;
    //         } elseif ($key == Contact::SEND_BCC) {
    //             $contactEmailBcc = $value;
    //         }
    //     }

    //     // We set the recipient mail according the type
    //     $type = $contact->getType();
        
    //     // Determine the right email according the type
    //     switch ($type) {
    //         case Contact::SUPPORT_CONTACT:
    //             $email->setRecipientEmail($this->supportEmailAddress);
    //             $email->setObject($this->supportEmailObject);
    //             break;
    //         case Contact::SIMPLE_CONTACT:
    //         default:
    //             $email->setRecipientEmail($contactEmail);
    //             if (count($contactEmailCc) > 0) {
    //                 $email->setRecipientEmailCc($contactEmailCc);
    //             }
    //             if (count($contactEmailBcc) > 0) {
    //                 $email->setRecipientEmailBcc($contactEmailBcc);
    //             }
    //             $email->setObject($this->contactEmailObject);
    //     }
    //     $email->setSenderEmail($contact->getEmail());
    //     $email->setReturnEmail($contact->getEmail());
    //     $email->setSenderFirstName($contact->getGivenName());
    //     $email->setSenderName($contact->getFamilyName());

    //     $this->emailManager->send($email, $this->emailTemplatePath . 'contact_email_posted', ['contact' => $contact]);
    // }

    /**
     * Executed when a contact message is sent
     *
     * @param ContactEmailEvent $event
     */
    public function onContactSent(ContactEmailEvent $event)
    {
        /** @var Contact $contact */
        $contact = $event->getContact();

        $email = new Email();
        // Recipients
        if (!is_array($contact->getContactType()->getTo()) && count($contact->getContactType()->getTo())>0) {
            $email->setRecipientEmail($contact->getContactType()->getTo());
        }
        if (!is_array($contact->getContactType()->getCc()) && count($contact->getContactType()->getCc())>0) {
            $email->setRecipientEmailCc($contact->getContactType()->getCc());
        }
        if (!is_array($contact->getContactType()->getBcc()) && count($contact->getContactType()->getBcc())>0) {
            $email->setRecipientEmailBcc($contact->getContactType()->getBcc());
        }

        // Object
        
        $email->setObject($this->platformName);
        
        // Sender
        $email->setSenderEmail($contact->getEmail());
        $email->setReturnEmail($contact->getEmail());
        $email->setSenderFirstName($contact->getGivenName());
        $email->setSenderName($contact->getFamilyName());
        

        echo "yoooo";
        die;

        //$this->emailManager->send($email, $this->emailTemplatePath . 'contact_email_posted', ['contact' => $contact]);
    }
}
