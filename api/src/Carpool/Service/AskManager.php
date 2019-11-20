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

use App\Carpool\Entity\Ad;
use App\Carpool\Entity\Ask;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskUpdatedEvent;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Carpool\Repository\MatchingRepository;
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
    private $matchingRepository;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager, MatchingRepository $matchingRepository, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->matchingRepository = $matchingRepository;
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
     * @param bool $formal Create a formal ask
     * @param Ask $askOpposite Opposite ask if relevant
     */
    public function createAskFromMatchedProposal(Proposal $proposal, Matching $matching, bool $formal=false, ?Ask $askOpposite = null)
    {
        $ask = new Ask();
        if ($formal) {
            // if it's a formal ask, the status is pending
            $ask->setStatus(Ask::STATUS_PENDING);
        } else {
            // if it's not a formal ask, the status is initiated
            $ask->setStatus(Ask::STATUS_INITIATED);
        }
        $ask->setType($proposal->getType());
        $ask->setUser($proposal->getUser());
        $ask->setMatching($matching);

        // we use the matching criteria
        $criteria = clone $matching->getCriteria();
        $ask->setCriteria($criteria);
        
        // we use the matching waypoints
        $waypoints = $matching->getWaypoints();
        foreach ($waypoints as $waypoint) {
            $ask->addWaypoint($waypoint);
        }

        if ($proposal->getAskLinked()) {
            // there's already an ask linked to the proposal, it's the return trip
            $ask->setAskLinked($proposal->getAskLinked());
        } else {
            // Ask History
            $askHistory = new AskHistory();
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());
            $ask->addAskHistory($askHistory);
        }

        if ($askOpposite) {
            $ask->setAskOpposite($askOpposite);
        }
        
        return $this->createAsk($ask);
    }

    /**
     * Create an ask from an ad.
     *
     * @param Ad $ad        The ad used to create the ask
     * @param bool $formal  The ask is a formal ask
     * @return Ad
     */
    public function createAskFromAd(Ad $ad, bool $formal)
    {
        $ask = new Ask();
        $matching = $this->matchingRepository->find($ad->getMatchingId());
        
        if ($ad->getAdId() == $matching->getProposalOffer()->getId()) {
            // the carpooler is the driver, the requester is the passenger
            $ask->setType($matching->getProposalRequest()->getType());
            $ask->setUser($matching->getProposalRequest()->getUser());
        } else {
            // the carpooler is the passenger, the requester is the driver
            $ask->setType($matching->getProposalOffer()->getType());
            $ask->setUser($matching->getProposalOffer()->getUser());
        }
        
        if ($formal) {
            // if it's a formal ask, the status is pending, depending on the role
            $ask->setStatus($ad->getRole() == Ad::ROLE_DRIVER ? Ask::STATUS_PENDING_AS_DRIVER : Ask::STATUS_PENDING_AS_PASSENGER);
        } else {
            // if it's not a formal ask, the status is initiated
            $ask->setStatus(Ask::STATUS_INITIATED);
        }

        $ask->setMatching($matching);

        // we use the matching criteria
        $criteria = clone $matching->getCriteria();
        $ask->setCriteria($criteria);
        
        // we use the matching waypoints
        $waypoints = $matching->getWaypoints();
        foreach ($waypoints as $waypoint) {
            $ask->addWaypoint($waypoint);
        }

        $this->entityManager->persist($ask);
        $this->entityManager->flush($ask);
        
        // dispatch en event
        $event = new AskPostedEvent($ask);
        $this->eventDispatcher->dispatch(AskPostedEvent::NAME, $event);
        
        return $ad;
    }
}
