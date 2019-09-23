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

namespace App\Carpool\Service;

use App\Carpool\Entity\Ask;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskUpdatedEvent;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;

/**
 * Ask manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class AskManager
{
    private $eventDispatcher;
    private $entityManager;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    
    /**
     * Create an ask.
     *
     */
    public function createAsk(Ask $ask)
    {
        // todo : check if an ask already exists for the match and the proposals
        
        $this->entityManager->persist($ask);
        // dispatch en event
        $event = new AskPostedEvent($ask);
        $this->eventDispatcher->dispatch(AskPostedEvent::NAME, $event);
        return $ask;
    }

    /**
     * Update an ask.
     *
     */
    public function updateAsk(Ask $ask)
    {
        // todo : check if an ask already exists for the match and the proposals
        
        $this->entityManager->persist($ask);

        $this->createAssociatedAskHistory($ask);

        // dispatch en event
        $event = new AskUpdatedEvent($ask);
        $this->eventDispatcher->dispatch(AskUpdatedEvent::NAME, $event);
        return $ask;
    }

    /**
     * Create the associated AskHistory of an Ask
     */
    private function createAssociatedAskHistory(Ask $ask)
    {
        $askHistory = new AskHistory();
        
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $askHistory->setAsk($ask);

        $this->entityManager->persist($askHistory);

        return $askHistory;
    }

    /**
     * Create an ask from already matched Proposal
     * @param Proposal $proposal The new Proposal
     * @param Matching $matching between those two proposals
     */
    public function createAskFromMatchedProposal(Proposal $proposal, Matching $matching)
    {
        $ask = new Ask();
        $ask->setStatus(Ask::STATUS_INITIATED); // Initial state of an Ask
        $ask->setType($proposal->getType());
        $ask->setUser($proposal->getUser());
        $ask->setMatching($matching);
        $ask->setCriteria($proposal->getCriteria());
        
        // We are using the new proposal waypoints to create the Ask's waypoints
        $waypoints = $proposal->getWaypoints();
        foreach ($waypoints as $waypoint) {
            $ask->addWaypoint($waypoint);
        }

        // Ask History
        $askHistory = new AskHistory();
        $askHistory->setStatus(1);
        $askHistory->setType($ask->getType());
        // We need an empty initial message however it would not appear in the mailbox
        $message = new Message();
        $message->setText("");
        $message->setUser($proposal->getUser());

        // I'm setting the right receipient
        $recipient = new Recipient();
        $recipient->setStatus(Recipient::STATUS_READ);
        $recipient->setUser($proposal->getMatchedProposal()->getUser());
        $message->addRecipient($recipient);

        $askHistory->setMessage($message);


        $ask->addAskHistory($askHistory);
        
        return $this->createAsk($ask);
    }
}
