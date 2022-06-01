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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Event\AdRenewalEvent;
use App\Carpool\Event\AskAcceptedEvent;
use App\Carpool\Event\AskAdDeletedEvent;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskRefusedEvent;
// use App\Carpool\Event\AskUpdatedEvent;
use App\Carpool\Event\DriverAskAdDeletedEvent;
use App\Carpool\Event\DriverAskAdDeletedUrgentEvent;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Event\PassengerAskAdDeletedEvent;
use App\Carpool\Event\PassengerAskAdDeletedUrgentEvent;
use App\Carpool\Event\ProposalCanceledEvent;
use App\Carpool\Event\AdMajorUpdatedEvent;
use App\Carpool\Event\AdMinorUpdatedEvent;
use App\Carpool\Event\ProposalPostedEvent;
use App\Carpool\Repository\AskHistoryRepository;
use App\Communication\Service\NotificationManager;
use App\TranslatorTrait;
use App\User\Entity\User;
use App\User\Service\BlockManager;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CarpoolSubscriber implements EventSubscriberInterface
{
    use TranslatorTrait;

    private $notificationManager;
    private $askHistoryRepository;
    private $logger;
    private $router;
    private $blockManager;

    public function __construct(
        NotificationManager $notificationManager,
        AskHistoryRepository $askHistoryRepository,
        LoggerInterface $logger,
        UrlGeneratorInterface $router,
        BlockManager $blockManager
    ) {
        $this->notificationManager = $notificationManager;
        $this->askHistoryRepository = $askHistoryRepository;
        $this->logger = $logger;
        $this->router = $router;
        $this->blockManager = $blockManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AskPostedEvent::NAME => 'onAskPosted',
            AskAcceptedEvent::NAME => 'onAskAccepted',
            AskRefusedEvent::NAME => 'onAskRefused',
            MatchingNewEvent::NAME => 'onNewMatching',
            AdRenewalEvent::NAME => 'onAdRenewal',
            ProposalPostedEvent::NAME => 'onProposalPosted',
            ProposalCanceledEvent::NAME => 'onProposalCanceled',
            //AskUpdatedEvent::NAME => 'onAskUpdated',  // Is this really usefull ?
            AskAdDeletedEvent::NAME => 'onAskAdDeleted',
            PassengerAskAdDeletedEvent::NAME => 'onPassengerAskAdDeleted',
            PassengerAskAdDeletedUrgentEvent::NAME => 'onPassengerAskAdDeletedUrgent',
            DriverAskAdDeletedEvent::NAME => 'onDriverAskAdDeleted',
            DriverAskAdDeletedUrgentEvent::NAME => 'onDriverAskAdDeletedUrgent',
            AdMinorUpdatedEvent::NAME => 'onAdMinorUpdated',
            AdMajorUpdatedEvent::NAME => 'onAdMajorUpdated'
        ];
    }

    /**
     * Executed when a new ask is posted
     *
     * @param AskPostedEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onAskPosted(AskPostedEvent $event): void
    {
        // the recipient is the carpooler
        $adRecipient = ($event->getAd()->getResults()[0]->getCarpooler());
        $this->notificationManager->notifies(AskPostedEvent::NAME, $adRecipient, $event->getAd());
    }

    /**
     * Executed when an ask is accepted
     *
     * @param AskAcceptedEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onAskAccepted(AskAcceptedEvent $event): void
    {
        // the recipient is the carpooler
        $adRecipient = ($event->getAd()->getResults()[0]->getCarpooler());
        $this->notificationManager->notifies(AskAcceptedEvent::NAME, $adRecipient, $event->getAd());
    }

    /**
     * Executed when an ask is declined
     *
     * @param AskRefusedEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onAskRefused(AskRefusedEvent $event): void
    {
        // the recipient is the carpooler
        $adRecipient = ($event->getAd()->getResults()[0]->getCarpooler());
        $this->notificationManager->notifies(AskRefusedEvent::NAME, $adRecipient, $event->getAd());
    }

    // /**
    //  * Executed when Ask is updated
    //  *
    //  * @param AskUpdatedEvent $event
    //  * @throws ClassNotFoundException
    //  */
    // public function onAskUpdated(AskUpdatedEvent $event)
    // {
    //     // we must notify the recipient of the ask, the message is related to the last accepted status of the ask history
    //     $lastAskHistory = $this->askHistoryRepository->findLastByAskAndstatus($event->getAsk(), Ask::STATUS_PENDING);
    //     // the recipient is the user that has made the last ask history
    //     // ATTENTION : Doesn't work because of ->getMessage(). There's not always a message with a askhistory
    //     $askRecipient = ($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $lastAskHistory->getMessage()->getUser()->getId()) ? $event->getAsk()->getMatching()->getProposalOffer()->getUser() : $event->getAsk()->getMatching()->getProposalRequest()->getUser();



    //     if ($this->canNotify($event->getAsk()->getUser(), $askRecipient)) {
    //         $this->notificationManager->notifies(AskUpdatedEvent::NAME, $askRecipient, $lastAskHistory);
    //     }
    // }

    /**
     * Executed when a new matching is discovered
     *
     * @param MatchingNewEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onNewMatching(MatchingNewEvent $event): void
    {
        // the recipient is the user that is not the "sender" of the matching
        // we check if it's not an anonymous proposal, and that it's only on an outward (as we notifiy only once for a return trip)
        if (
            $event->getMatching()->getProposalOffer()->getUser() &&
            $event->getMatching()->getProposalRequest()->getUser() &&
            $event->getWay() != Proposal::TYPE_RETURN
            ) {
            $askRecipient =
            ($event->getMatching()->getProposalOffer()->getUser()->getId() != $event->getSender()->getId()) ?
            $event->getMatching()->getProposalOffer()->getUser() :
            $event->getMatching()->getProposalRequest()->getUser();
            if ($this->canNotify($event->getSender(), $askRecipient)) {
                $this->notificationManager->notifies(MatchingNewEvent::NAME, $askRecipient, $event->getMatching());
            }
        }
    }

    /**
     * Execute when a proposal is posted.
     *
     * @param ProposalPostedEvent $event
     */
    public function onProposalPosted(ProposalPostedEvent $event)
    {
        // we check if it's not an anonymous proposal
        if ($event->getProposal()->getUser()) {
            $this->notificationManager->notifies(ProposalPostedEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
        }
    }

    /**
     * Execute when a proposal is canceled.
     *
     * @param ProposalPostedEvent $event
     */
    public function onProposalCanceled(ProposalCanceledEvent $event)
    {
        $this->notificationManager->notifies(ProposalCanceledEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
    }

    /**
     * Executed when an ad needs to be renewed
     *
     * @param AdRenewalEvent $event
     * @return void
     */
    public function onAdRenewal(AdRenewalEvent $event): void
    {
        // we must notify the creator of the proposal
        $this->notificationManager->notifies(AdRenewalEvent::NAME, $event->getProposal()->getUser());
    }

    /**
     * Executed when an ad is deleted with ask
     *
     * @param AskAdDeletedEvent $event
     * @return void
     */
    public function onAskAdDeleted(AskAdDeletedEvent $event): void
    {
        // todo: passer directement la ask pour pouvoir mieux vérifier qui est à l'origine de l'annonce
        // pas réussi, array vide depuis le template en passant la ask
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $this->notificationManager->notifies(AskAdDeletedEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $this->notificationManager->notifies(AskAdDeletedEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with driver accepted
     *
     * @param PassengerAskAdDeletedEvent $event
     * @return void
     */
    public function onPassengerAskAdDeleted(PassengerAskAdDeletedEvent $event): void
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $this->notificationManager->notifies(PassengerAskAdDeletedEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $this->notificationManager->notifies(PassengerAskAdDeletedEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with driver accepted and in less than 24h
     * @param PassengerAskAdDeletedUrgentEvent $event
     * @return void
     */
    public function onPassengerAskAdDeletedUrgent(PassengerAskAdDeletedUrgentEvent $event): void
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $this->notificationManager->notifies(PassengerAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $this->notificationManager->notifies(PassengerAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with passenger accepted
     *
     * @param DriverAskAdDeletedEvent $event
     * @return void
     */
    public function onDriverAskAdDeleted(DriverAskAdDeletedEvent $event): void
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $this->notificationManager->notifies(DriverAskAdDeletedEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $this->notificationManager->notifies(DriverAskAdDeletedEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with passenger accepted and in less than 24h
     *
     * @param DriverAskAdDeletedUrgentEvent $event
     * @return void
     */
    public function onDriverAskAdDeletedUrgent(DriverAskAdDeletedUrgentEvent $event): void
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $this->notificationManager->notifies(DriverAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $this->notificationManager->notifies(DriverAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    public function onAdMinorUpdated(AdMinorUpdatedEvent $event)
    {
        $object = (object) [
            "old" => $event->getOldAd(),
            "new" => $event->getNewAd(),
            "sender" => $event->getSender()
        ];

        foreach ($event->getAsks() as $ask) {
            $object->ask = $ask;
            if ($this->canNotify($ask->getUser(), $ask->getUserRelated())) {
                $this->notificationManager->notifies(AdMinorUpdatedEvent::NAME, $ask->getUser(), $object);
            }
        }
    }

    public function onAdMajorUpdated(AdMajorUpdatedEvent $event)
    {
        $object = (object) [
            "old" => $event->getOldAd(),
            "new" => $event->getNewAd(),
            "sender" => $event->getSender()
        ];

        foreach ($event->getAsks() as $ask) {
            $object->ask = $ask;
            $origin = null;
            $destination = null;
            $regular = false;
            $date = null;

            if ($ask->getCriteria()->getFrequency() === 2) {
                $regular = true;
            } else {
                $date = $ask->getCriteria()->getFromDate();
                !is_null($date) ? $date = $date->format('Y-m-d') : null;
            }
            /** @var Waypoint $waypoint */
            foreach ($ask->getWaypoints() as $waypoint) {
                if ($waypoint->getPosition() === 0) {
                    $origin = clone $waypoint->getAddress();
                } elseif ($waypoint->isDestination()) {
                    $destination = clone $waypoint->getAddress();
                }
            }

            $routeParams = [
                "origin" => json_encode($origin),
                "destination" => json_encode($destination),
                "regular" => $regular,
                "date" => $date
            ];
            // todo: use if we can keep the proposal (request or offer) if we delete the matched one
//            if ($ask->getCriteria()->isDriver()) {
//                $proposalId = $ask->getMatching()->getProposalOffer()->getId();
//            } else {
//                $proposalId = $ask->getMatching()->getProposalRequest()->getId();
//            }
//            $routeParams = ["pid" => $proposalId];
            $object->searchLink = $event->getMailSearchLink() . "?" . http_build_query($routeParams);
            if ($this->canNotify($ask->getUser(), $ask->getUserRelated())) {
                $this->notificationManager->notifies(AdMajorUpdatedEvent::NAME, $ask->getUser(), $object);
            }
        }
    }

    /**
     * Determine if the User1 can notify the User2 (i.e. Not involved in a block)
     *
     * @param User $user1
     * @param User $user2
     * @return boolean
     */
    public function canNotify(User $user1, User $user2): bool
    {
        $blocks = $this->blockManager->getInvolvedInABlock($user1, $user2);
        if (is_array($blocks) && count($blocks)>0) {
            return false;
        }
        return true;
    }
}
