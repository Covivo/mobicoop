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

namespace App\Communication\Service;

use App\Communication\Entity\Contact;
use App\Communication\Event\ContactEmailEvent;
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
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, array $contactItems)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->contactItems = $contactItems;
    }

    /**
     * Send email event for contact message.
     *
     * @return Contact
     */
    public function sendContactMail(Contact $contact): Contact
    {
        // Get the contact type of this contact
        $contactTypes = $this->getContactTypes();
        foreach ($contactTypes as $contactType) {
            if ($contactType->getDemand() == $contact->getDemand()) {
                $contact->setContactType($contactType);
            }
        }

        if (is_null($contact->getContactType())) {
            throw new \LogicException('Unknown contact demand');
        }

        $event = new ContactEmailEvent($contact);
        $this->eventDispatcher->dispatch($event, ContactEmailEvent::NAME);

        return $contact;
    }

    /**
     * Get the ContactTypes.
     *
     * @return ContactType[]
     */
    public function getContactTypes(): array
    {
        $contactTypes = [];
        foreach ($this->contactItems['contacts'] as $contactItem) {
            $contactTypes[] = $this->buildContactType($contactItem);
        }

        return $contactTypes;
    }

    /**
     * Return the specific email list of a type.
     */
    public function getEmailsByType(string $type): ?ContactType
    {
        // Get the contact types and find the support
        $contactTypes = $this->getContactTypes();
        foreach ($contactTypes as $contactType) {
            if ($contactType->getDemand() == $type) {
                return $contactType;
            }
        }

        return null;
    }

    /**
     * Build a ContactType from a contact item in config contacts.json.
     */
    private function buildContactType(array $item)
    {
        $contactType = new ContactType();
        $contactType->setDemand($item['label']);
        $contactType->setObjectCode($item['objectCode']);
        $contactType->setTo($item['To']);
        $contactType->setCc($item['Cc']);
        $contactType->setBcc($item['Bcc']);

        return $contactType;
    }
}
