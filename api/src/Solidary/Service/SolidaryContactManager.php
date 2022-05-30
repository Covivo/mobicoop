<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Communication\Entity\Medium;
use App\Communication\Entity\Message;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\Solidary\Entity\SolidaryContact;
use App\Solidary\Event\SolidaryContactEmailEvent;
use App\Solidary\Event\SolidaryContactMessageEvent;
use App\Solidary\Event\SolidaryContactSmsEvent;
use App\Solidary\Repository\SolidaryAskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class SolidaryContactManager
{
    private $entityManager;
    private $eventDispatcher;
    private $security;
    private $solidaryAskRepository;
    private $solidaryAskManager;
    private $notificationsEnabled;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryAskRepository $solidaryAskRepository, SolidaryAskManager $solidaryAskManager, bool $notificationsEnabled)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->solidaryAskRepository = $solidaryAskRepository;
        $this->solidaryAskManager = $solidaryAskManager;
        $this->notificationsEnabled = $notificationsEnabled;
    }

    /**
     * Handle a SolidaryContact.
     *
     * @return SolidaryContact
     */
    public function handleSolidaryContact(SolidaryContact $solidaryContact)
    {
        // We check if there is already an Ask for the solidarySolution in the SolidaryContact
        $solidaryAsk = $this->solidaryAskRepository->findBySolidarySolution($solidaryContact->getSolidarySolution());

        if (empty($solidaryAsk)) {
            // There is no SolidaryAsk we need to create it before trigger the event
            $solidaryAsk = new SolidaryAsk();
            $solidaryAsk->setStatus(SolidaryAsk::STATUS_ASKED);
            $solidaryAsk->setSolidarySolution($solidaryContact->getSolidarySolution());
            $criteria = clone $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getCriteria();
            $solidaryAsk->setCriteria($criteria);
            $solidaryAsk = $this->solidaryAskManager->createSolidaryAsk($solidaryAsk);
        } else {
            // We found the solidaryAsk
            $solidaryAsk = $solidaryAsk[0];
        }

        // we trigger the solidaryContact events
        $media = $solidaryContact->getMedia();
        if ($this->notificationsEnabled) {
            foreach ($media as $medium) {
                switch ($medium->getId()) {
                    case Medium::MEDIUM_MESSAGE:
                        // We create the message
                        $message = $this->buildInternalMessage($solidaryContact);
                        $solidaryContact->setMessage($message);
                        $event = new SolidaryContactMessageEvent($solidaryContact);
                        $this->eventDispatcher->dispatch($event, SolidaryContactMessageEvent::NAME);
                        // We create the SolidaryAskHistory and AskHistory if needed
                        $this->createHistories($solidaryAsk, $solidaryContact->getMessage());

                        break;

                    case Medium::MEDIUM_SMS:
                        $event = new SolidaryContactSmsEvent($solidaryContact);
                        $this->eventDispatcher->dispatch($event, SolidaryContactSmsEvent::NAME);

                        break;

                    case Medium::MEDIUM_EMAIL:
                        $event = new SolidaryContactEmailEvent($solidaryContact);
                        $this->eventDispatcher->dispatch($event, SolidaryContactEmailEvent::NAME);

                        break;
                }
            }
        }
        // We set the solidaryAsk field for the return
        $solidaryContact->setSolidaryAsk($solidaryAsk);

        return $solidaryContact;
    }

    /**
     * Build a simple message to send.
     */
    private function buildInternalMessage(SolidaryContact $object): Message
    {
        $message = new Message();
        $message->setUser($object->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser());
        // we set the user delegate if the message is send by an solidary operator
        if ($this->security->getUser()->getId() !== $message->getUser()->getId()) {
            $message->setUserDelegate($this->security->getUser());
        }
        $message->setText($object->getContent());

        if ($object->getSolidarySolution()->getSolidaryAsk()) {
            // If there is already a message in the thread, we need to set it
            $solidaryAskHistories = $object->getSolidarySolution()->getSolidaryAsk()->getSolidaryAskHistories();
            if (!is_null($solidaryAskHistories)) {
                foreach ($solidaryAskHistories as $solidaryAskHistory) {
                    if (!is_null($solidaryAskHistory->getMessage())) {
                        $message->setMessage($solidaryAskHistory->getMessage());

                        break;
                    }
                }
            }
        }

        return $message;
    }

    /**
     * We create the SolidaryAskHistory and AskHistory if needed linked to a Message.
     */
    private function createHistories(SolidaryAsk $solidaryAsk, Message $message)
    {
        // if there is a solidaryAsk we persist a new SolidaryAskHistory linked to this message
        if (!is_null($solidaryAsk)) {
            $solidaryAskHistory = new SolidaryAskHistory();
            $solidaryAskHistory->setStatus($solidaryAsk->getStatus());
            $solidaryAskHistory->setSolidaryAsk($solidaryAsk);
            $solidaryAskHistory->setMessage($message);
            $this->entityManager->persist($solidaryAskHistory);
            $this->entityManager->flush();
        }

        // if there is an Ask we persist a new SolidaryAskHistory linked to this message
        $ask = $solidaryAsk->getAsk();
        if (!is_null($ask)) {
            $askHistory = new AskHistory();
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());
            $askHistory->setAsk($ask);
            $askHistory->setMessage($message);
            $this->entityManager->persist($askHistory);
            $this->entityManager->flush();
        }
    }
}
