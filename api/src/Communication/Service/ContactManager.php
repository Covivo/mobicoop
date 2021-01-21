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


namespace App\Communication\Service;

use App\Communication\Event\ContactEmailEvent;
use App\Communication\Entity\Contact;
use App\Communication\Ressource\ContactType;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContactManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $contactItems;

    /**
     * ContactManager constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, array $contactItems)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->contactItems = $contactItems;
    }

    /**
     * Send email event for contact message
     *
     * @param Contact $contact
     * @return Contact
     */
    public function sendContactMail(Contact $contact)
    {
        $event = new ContactEmailEvent($contact);
        $this->eventDispatcher->dispatch(ContactEmailEvent::NAME, $event);
        return $contact;
    }

    public function getContactTypes(): ?array
    {
        $contactTypes = [];
        foreach ($this->contactItems['contacts'] as $contactItem) {
            $contactType = new ContactType();
            $contactType->setDemand($contactItem['label']);
            $contactType->setTo($contactItem['To']);
            $contactType->setCc($contactItem['Cc']);
            $contactType->setBcc($contactItem['Bcc']);
            $contactTypes[] = $contactType;
        }
        return $contactTypes;
    }
}
