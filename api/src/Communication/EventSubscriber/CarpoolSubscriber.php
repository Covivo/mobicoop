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

use App\Carpool\Event\ProposalPostedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Communication\Service\NotificationManager;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskAcceptedEvent;
use App\Carpool\Event\AskRefusedEvent;
use App\Carpool\Repository\AskHistoryRepository;
use App\Carpool\Entity\Ask;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Event\AdRenewalEvent;

class CarpoolSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $askHistoryRepository;

    public function __construct(NotificationManager $notificationManager, AskHistoryRepository $askHistoryRepository)
    {
        $this->notificationManager = $notificationManager;
        $this->askHistoryRepository = $askHistoryRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            AskPostedEvent::NAME => 'onAskPosted',
            AskAcceptedEvent::NAME => 'onAskAccepted',
            AskRefusedEvent::NAME => 'onAskRefused',
            MatchingNewEvent::NAME => 'onNewMatching',
            AdRenewalEvent::NAME => 'onAdRenewal',
				    ProposalPostedEvent::NAME => 'onProposalPosted'
        ];
    }

    /**
     * Executed when a new ask is posted
     *
     * @param AskPostedEvent $event
     * @return void
     */
    public function onAskPosted(AskPostedEvent $event)
    {
        // we must notify the recipient of the ask
        if ($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId()) {
            // the recipient is the driver, the message is related to the first ask history
            $this->notificationManager->notifies(AskPostedEvent::NAME, $event->getAsk()->getMatching()->getProposalOffer()->getUser(), $event->getAsk()->getAskHistories()[0]);
        } else {
            // the recipient is the passenger, the message is related to the first ask history
            $this->notificationManager->notifies(AskPostedEvent::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $event->getAsk()->getAskHistories()[0]);
        }
    }

    /**
     * Executed when an ask is accepted
     *
     * @param AskAcceptedEvent $event
     * @return void
     */
    public function onAskAccepted(AskAcceptedEvent $event)
    {
        // we must notify the recipient of the ask, the message is related to the last accepted status of the ask history
        $lastAskHistory = $this->AskHistoryRepository->findLastByAskAndstatus($event->getAsk(), Ask::STATUS_ACCEPTED);
        if ($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId()) {
            // the recipient is the driver
            $this->notificationManager->notifies(AskAcceptedEvent::NAME, $event->getAsk()->getMatching()->getProposalOffer()->getUser(), $lastAskHistory);
        } else {
            // the recipient is the passenger
            $this->notificationManager->notifies(AskAcceptedEvent::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $lastAskHistory);
        }
    }

    /**
     * Executed when an ask is declined
     *
     * @param AskRefusedEvent $event
     * @return void
     */
    public function onAskRefused(AskRefusedEvent $event)
    {
        // we must notify the recipient of the ask, the message is related to the last refused status of the ask history
        $lastAskHistory = $this->AskHistoryRepository->findLastByAskAndstatus($event->getAsk(), Ask::STATUS_DECLINED);
        if ($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId()) {
            // the recipient is the driver
            $this->notificationManager->notifies(AskRefusedEvent::NAME, $event->getAsk()->getMatching()->getProposalOffer()->getUser(), $lastAskHistory);
        } else {
            // the recipient is the passenger
            $this->notificationManager->notifies(AskRefusedEvent::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $lastAskHistory);
        }
    }

    /**
     * Executed when a new matching is discovered
     *
     * @param MatchingNewEvent $event
     * @return void
     */
    public function onNewMatching(MatchingNewEvent $event)
    {
        // we must notify the user that posted the first proposal of the matching
        if ($event->getMatching()->getProposalOffer()->getCreatedDate() < $event->getMatching()->getProposalRequest()->getCreatedDate()) {
            // the recipient is the driver
            $this->notificationManager->notifies(MatchingNewEvent::NAME, $event->getMatching()->getProposalOffer()->getUser());
        } else {
            // the recipient is the passenger
            $this->notificationManager->notifies(MatchingNewEvent::NAME, $event->getMatching()->getProposalRequest()->getUser());
        }
    }
 
 /**
	* Execute when proposal is posted.
	*
	* @param ProposalPostedEvent $event
	*/
		public function onProposalPosted(ProposalPostedEvent $event)
		{
			$this->notificationManager->notifies(ProposalPostedEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
		}

    /**
     * Executed when an ad needs to be renewed
     *
     * @param AdRenewalEvent $event
     * @return void
     */
    public function onAdRenewal(AdRenewalEvent $event)
    {
        // we must notify the creator of the proposal
        $this->notificationManager->notifies(AdRenewalEvent::NAME, $event->getProposal()->getUser());
    }
}
