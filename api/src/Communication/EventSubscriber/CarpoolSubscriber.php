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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Event\AdMajorUpdatedEvent;
use App\Carpool\Event\AdMinorUpdatedEvent;
use App\Carpool\Event\AdRenewalEvent;
// use App\Carpool\Event\AskUpdatedEvent;
use App\Carpool\Event\AskAcceptedEvent;
use App\Carpool\Event\AskAdDeletedEvent;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskRefusedEvent;
use App\Carpool\Event\CarpoolAskPostedRelaunch1Event;
use App\Carpool\Event\CarpoolAskPostedRelaunch2Event;
use App\Carpool\Event\DriverAskAdDeletedEvent;
use App\Carpool\Event\DriverAskAdDeletedUrgentEvent;
use App\Carpool\Event\InactiveAdRelaunchEvent;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Event\PassengerAskAdDeletedEvent;
use App\Carpool\Event\PassengerAskAdDeletedUrgentEvent;
use App\Carpool\Event\ProposalCanceledEvent;
use App\Carpool\Event\ProposalPostedEvent;
use App\Carpool\Event\ProposalWillExpireEvent;
use App\Carpool\Repository\AskHistoryRepository;
use App\Carpool\Service\AskManager;
use App\Communication\Service\NotificationManager;
use App\TranslatorTrait;
use App\User\Entity\User;
use App\User\Event\ConfirmedCarpoolerEvent;
use App\User\Service\BlockManager;
use App\User\Service\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CarpoolSubscriber implements EventSubscriberInterface
{
    use TranslatorTrait;

    private $notificationManager;
    private $askHistoryRepository;
    private $logger;
    private $router;
    private $blockManager;
    private $askManager;
    private $userManager;

    public function __construct(
        NotificationManager $notificationManager,
        AskHistoryRepository $askHistoryRepository,
        LoggerInterface $logger,
        UrlGeneratorInterface $router,
        BlockManager $blockManager,
        AskManager $askManager,
        UserManager $userManager
    ) {
        $this->notificationManager = $notificationManager;
        $this->askHistoryRepository = $askHistoryRepository;
        $this->logger = $logger;
        $this->router = $router;
        $this->blockManager = $blockManager;
        $this->askManager = $askManager;
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            AskPostedEvent::NAME => 'onAskPosted',
            AskAcceptedEvent::NAME => 'onAskAccepted',
            AskRefusedEvent::NAME => 'onAskRefused',
            MatchingNewEvent::NAME => 'onNewMatching',
            AdRenewalEvent::NAME => 'onAdRenewal',
            ProposalPostedEvent::NAME => 'onProposalPosted',
            ProposalCanceledEvent::NAME => 'onProposalCanceled',
            // AskUpdatedEvent::NAME => 'onAskUpdated',  // Is this really usefull ?
            AskAdDeletedEvent::NAME => 'onAskAdDeleted',
            PassengerAskAdDeletedEvent::NAME => 'onPassengerAskAdDeleted',
            PassengerAskAdDeletedUrgentEvent::NAME => 'onPassengerAskAdDeletedUrgent',
            DriverAskAdDeletedEvent::NAME => 'onDriverAskAdDeleted',
            DriverAskAdDeletedUrgentEvent::NAME => 'onDriverAskAdDeletedUrgent',
            AdMinorUpdatedEvent::NAME => 'onAdMinorUpdated',
            AdMajorUpdatedEvent::NAME => 'onAdMajorUpdated',
            AskPostedEvent::NAME => 'onAskPosted',
            CarpoolAskPostedRelaunch1Event::NAME => 'onCarpoolAskPostedRelaunch1',
            CarpoolAskPostedRelaunch2Event::NAME => 'onCarpoolAskPostedRelaunch2',
            ProposalWillExpireEvent::NAME => 'onProposalWillExpire',
            InactiveAdRelaunchEvent::NAME => 'onInactiveAdRelaunch',
        ];
    }

    /**
     * Executed when a new ask is posted.
     *
     * @throws ClassNotFoundException
     */
    public function onAskPosted(AskPostedEvent $event)
    {
        $event->getAd()->setSchedule($this->addSchedule($event->getAd()));
        $adRecipient = $event->getAd()->getResults()[0]->getCarpooler();
        $this->notificationManager->notifies(AskPostedEvent::NAME, $adRecipient, $event->getAd());
    }

    /**
     * Executed when an ask is accepted.
     *
     * @throws ClassNotFoundException
     */
    public function onAskAccepted(AskAcceptedEvent $event)
    {
        $event->getAd()->setSchedule($this->addSchedule($event->getAd()));
        // we send the email to requester of the carpool
        $adRecipient = $event->getAd()->getResults()[0]->getCarpooler();
        $this->notificationManager->notifies(AskAcceptedEvent::NAME, $adRecipient, $event->getAd());
        //  we also send the eail to the offerer of the carpool
        $adRecipient = $this->userManager->getUser($event->getAd()->getuserId());
        $this->notificationManager->notifies(AskAcceptedEvent::NAME, $adRecipient, $event->getAd());
    }

    /**
     * Executed when an ask is declined.
     *
     * @throws ClassNotFoundException
     */
    public function onAskRefused(AskRefusedEvent $event)
    {
        $event->getAd()->setSchedule($this->addSchedule($event->getAd()));
        // we send the email to requester of the carpool
        $adRecipient = $event->getAd()->getResults()[0]->getCarpooler();
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
     * Executed when a new matching is discovered.
     *
     * @throws ClassNotFoundException
     */
    public function onNewMatching(MatchingNewEvent $event)
    {
        // the recipient is the user that is not the "sender" of the matching
        // we check if it's not an anonymous proposal, and that it's only on an outward (as we notifiy only once for a return trip)
        if (
            $event->getMatching()->getProposalOffer()->getUser()
            && $event->getMatching()->getProposalRequest()->getUser()
            && Proposal::TYPE_RETURN != $event->getWay()
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
     */
    public function onProposalPosted(ProposalPostedEvent $event)
    {
        $user = $event->getProposal()->getUser();

        if (is_null($user)) {
            return;
        }

        if (5 == count($user->getProposals())) {
            $event = new ConfirmedCarpoolerEvent($user);
            $this->eventDispatcher->dispatch(ConfirmedCarpoolerEvent::NAME, $event);
        }
        // we check if it's not an anonymous proposal
        // if ($event->getProposal()->getUser()) {
        //     $this->notificationManager->notifies(ProposalPostedEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
        // }
    }

    public function onProposalCanceled(ProposalCanceledEvent $event)
    {
        $this->notificationManager->notifies(ProposalCanceledEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
    }

    public function onProposalWillExpire(ProposalWillExpireEvent $event)
    {
        $this->notificationManager->notifies(ProposalWillExpireEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
    }

    public function onInactiveAdRelaunch(ProposalWillExpireEvent $event)
    {
        $this->notificationManager->notifies(ProposalWillExpireEvent::NAME, $event->getProposal()->getUser(), $event->getProposal());
    }

    /**
     * Executed when an ad needs to be renewed.
     */
    public function onAdRenewal(AdRenewalEvent $event)
    {
        // we must notify the creator of the proposal
        $this->notificationManager->notifies(AdRenewalEvent::NAME, $event->getProposal()->getUser());
    }

    /**
     * Executed when an ad is deleted with ask.
     */
    public function onAskAdDeleted(AskAdDeletedEvent $event)
    {
        // todo: passer directement la ask pour pouvoir mieux vérifier qui est à l'origine de l'annonce
        // pas réussi, array vide depuis le template en passant la ask
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUserRelated()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(AskAdDeletedEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(AskAdDeletedEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with driver accepted.
     */
    public function onPassengerAskAdDeleted(PassengerAskAdDeletedEvent $event)
    {
        // todo : idem

        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                // get the complete ad to have data for the email
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUserRelated()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(PassengerAskAdDeletedEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(PassengerAskAdDeletedEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with driver accepted and in less than 24h.
     */
    public function onPassengerAskAdDeletedUrgent(PassengerAskAdDeletedUrgentEvent $event)
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUserRelated()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(PassengerAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(PassengerAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with passenger accepted.
     */
    public function onDriverAskAdDeleted(DriverAskAdDeletedEvent $event)
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUserRelated()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(DriverAskAdDeletedEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(DriverAskAdDeletedEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    /**
     * Executed when an ad is deleted with passenger accepted and in less than 24h.
     */
    public function onDriverAskAdDeletedUrgent(DriverAskAdDeletedUrgentEvent $event)
    {
        // todo : idem
        if ($this->canNotify($event->getAsk()->getUser(), $event->getAsk()->getUserRelated())) {
            if ($event->getAsk()->getUser()->getId() == $event->getDeleterId()) {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUserRelated()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(DriverAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUserRelated(), $event->getAsk());
            } else {
                $ad = $this->askManager->getAskFromAd($event->getAsk()->getId(), $event->getAsk()->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $event->getAsk()->setAd($ad);
                $this->notificationManager->notifies(DriverAskAdDeletedUrgentEvent::NAME, $event->getAsk()->getUser(), $event->getAsk());
            }
        }
    }

    public function onAdMinorUpdated(AdMinorUpdatedEvent $event)
    {
        $object = (object) [
            'old' => $event->getOldAd(),
            'new' => $event->getNewAd(),
            'sender' => $event->getSender(),
        ];

        foreach ($event->getAsks() as $ask) {
            $object->ask = $ask;
            if ($this->canNotify($ask->getUser(), $ask->getUserRelated())) {
                $ad = $this->askManager->getAskFromAd($ask->getId(), $ask->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $ask->setAd($ad);
                $this->notificationManager->notifies(AdMinorUpdatedEvent::NAME, $ask->getUser(), $object);
            }
        }
    }

    public function onAdMajorUpdated(AdMajorUpdatedEvent $event)
    {
        $object = (object) [
            'old' => $event->getOldAd(),
            'new' => $event->getNewAd(),
            'sender' => $event->getSender(),
        ];

        foreach ($event->getAsks() as $ask) {
            $object->ask = $ask;
            $origin = null;
            $destination = null;
            $regular = false;
            $date = null;

            if (2 === $ask->getCriteria()->getFrequency()) {
                $regular = true;
            } else {
                $date = $ask->getCriteria()->getFromDate();
                !is_null($date) ? $date = $date->format('Y-m-d') : null;
            }

            /** @var Waypoint $waypoint */
            foreach ($ask->getWaypoints() as $waypoint) {
                if (0 === $waypoint->getPosition()) {
                    $origin = clone $waypoint->getAddress();
                } elseif ($waypoint->isDestination()) {
                    $destination = clone $waypoint->getAddress();
                }
            }

            $routeParams = [
                'origin' => json_encode($origin),
                'destination' => json_encode($destination),
                'regular' => $regular,
                'date' => $date,
            ];
            // todo: use if we can keep the proposal (request or offer) if we delete the matched one
//            if ($ask->getCriteria()->isDriver()) {
//                $proposalId = $ask->getMatching()->getProposalOffer()->getId();
//            } else {
//                $proposalId = $ask->getMatching()->getProposalRequest()->getId();
//            }
//            $routeParams = ["pid" => $proposalId];
            $object->searchLink = $event->getMailSearchLink().'?'.http_build_query($routeParams);
            if ($this->canNotify($ask->getUser(), $ask->getUserRelated())) {
                $ad = $this->askManager->getAskFromAd($ask->getId(), $ask->getUser()->getId());
                $ad->setSchedule($this->addSchedule($ad));
                $ask->setAd($ad);
                $this->notificationManager->notifies(AdMajorUpdatedEvent::NAME, $ask->getUser(), $object);
            }
        }
    }

    /**
     * Determine if the User1 can notify the User2 (i.e. Not involved in a block).
     */
    public function canNotify(User $user1, User $user2): bool
    {
        $blocks = $this->blockManager->getInvolvedInABlock($user1, $user2);
        if (is_array($blocks) && count($blocks) > 0) {
            return false;
        }

        return true;
    }

    public function onCarpoolAskPostedRelaunch1(CarpoolAskPostedRelaunch1Event $event)
    {
        $event->getAd()->setSchedule($this->addSchedule($event->getAd()));
        $adRecipient = $event->getAd()->getResults()[0]->getCarpooler();
        $this->notificationManager->notifies(CarpoolAskPostedRelaunch1Event::NAME, $adRecipient, $event->getAd());
    }

    public function onCarpoolAskPostedRelaunch2(CarpoolAskPostedRelaunch2Event $event)
    {
        $event->getAd()->setSchedule($this->addSchedule($event->getAd()));
        $adRecipient = $event->getAd()->getResults()[0]->getCarpooler();
        $this->notificationManager->notifies(CarpoolAskPostedRelaunch2Event::NAME, $adRecipient, $event->getAd());
    }

    public function addSchedule($ad)
    {
        $multipleSchedules = [];

        if (Criteria::FREQUENCY_PUNCTUAL == $ad->getFrequency()) {
            return $multipleSchedules;
        }
        if (${$ad}->getResults()[0]->getResultDriver()) {
            $outwardResult = $ad->getResults()[0]->getResultDriver()->getOutward();
            $returnResult = $ad->getResults()[0]->getResultDriver()->getReturn();
        } else {
            $outwardResult = $ad->getResults()[0]->getResultPassenger()->getOutward();
            $returnResult = $ad->getResults()[0]->getResultPassenger()->getReturn();
        }
        $askConcerned = $this->askManager->getAsk($ad->getAskId());

        $times = [];
        if (!in_array(($outwardResult->getMonTime() ? $outwardResult->getMonTime()->format('H:i') : 'null').' '.($returnResult->getMonTime() ? $returnResult->getMonTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getMonTime() ? $outwardResult->getMonTime()->format('H:i') : 'null').' '.($returnResult->getMonTime() ? $returnResult->getMonTime()->format('H:i') : 'null');
        }
        if (!in_array(($outwardResult->getTueTime() ? $outwardResult->getTueTime()->format('H:i') : 'null').' '.($returnResult->getTueTime() ? $returnResult->getTueTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getTueTime() ? $outwardResult->getTueTime()->format('H:i') : 'null').' '.($returnResult->getTueTime() ? $returnResult->getTueTime()->format('H:i') : 'null');
        }
        if (!in_array(($outwardResult->getWedTime() ? $outwardResult->getWedTime()->format('H:i') : 'null').' '.($returnResult->getWedTime() ? $returnResult->getWedTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getWedTime() ? $outwardResult->getWedTime()->format('H:i') : 'null').' '.($returnResult->getWedTime() ? $returnResult->getWedTime()->format('H:i') : 'null');
        }
        if (!in_array(($outwardResult->getThuTime() ? $outwardResult->getThuTime()->format('H:i') : 'null').' '.($returnResult->getThuTime() ? $returnResult->getThuTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getThuTime() ? $outwardResult->getThuTime()->format('H:i') : 'null').' '.($returnResult->getThuTime() ? $returnResult->getThuTime()->format('H:i') : 'null');
        }
        if (!in_array(($outwardResult->getFriTime() ? $outwardResult->getFriTime()->format('H:i') : 'null').' '.($returnResult->getFriTime() ? $returnResult->getFriTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getFriTime() ? $outwardResult->getFriTime()->format('H:i') : 'null').' '.($returnResult->getFriTime() ? $returnResult->getFriTime()->format('H:i') : 'null');
        }
        if (!in_array(($outwardResult->getSatTime() ? $outwardResult->getSatTime()->format('H:i') : 'null').' '.($returnResult->getSatTime() ? $returnResult->getSatTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getSatTime() ? $outwardResult->getSatTime()->format('H:i') : 'null').' '.($returnResult->getSatTime() ? $returnResult->getSatTime()->format('H:i') : 'null');
        }
        if (!in_array(($outwardResult->getSunTime() ? $outwardResult->getSunTime()->format('H:i') : 'null').' '.($returnResult->getSunTime() ? $returnResult->getSunTime()->format('H:i') : 'null'), $times)) {
            $times[] = ($outwardResult->getSunTime() ? $outwardResult->getSunTime()->format('H:i') : 'null').' '.($returnResult->getSunTime() ? $returnResult->getSunTime()->format('H:i') : 'null');
        }

        $schedule = [
            'outwardPickUpTime' => null,
            'outwardDropOffTime' => null,
            'returnPickUpTime' => null,
            'returnDropOffTime' => null,
            'monCheck' => false,
            'tueCheck' => false,
            'wedCheck' => false,
            'thuCheck' => false,
            'friCheck' => false,
            'satCheck' => false,
            'sunCheck' => false,
        ];
        foreach ($times as $time) {
            if ('null null' == $time) {
                continue;
            }
            if (($outwardResult->getMonTime() ? $outwardResult->getMonTime()->format('H:i') : 'null').' '.($returnResult->getMonTime() ? $returnResult->getMonTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getMonTime() ? clone $outwardResult->getMonTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getMonTime() ? clone $outwardResult->getMonTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getMonTime() ? clone $returnResult->getMonTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getMonTime() ? clone $returnResult->getMonTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['monCheck'] = true;
            }
            if (($outwardResult->getTueTime() ? $outwardResult->getTueTime()->format('H:i') : 'null').' '.($returnResult->getTueTime() ? $returnResult->getTueTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getTueTime() ? clone $outwardResult->getTueTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getTueTime() ? clone $outwardResult->getTueTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getTueTime() ? clone $returnResult->getTueTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getTueTime() ? clone $returnResult->getTueTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['tueCheck'] = true;
            }
            if (($outwardResult->getWedTime() ? $outwardResult->getWedTime()->format('H:i') : 'null').' '.($returnResult->getWedTime() ? $returnResult->getWedTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getWedTime() ? clone $outwardResult->getWedTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getWedTime() ? clone $outwardResult->getWedTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getWedTime() ? clone $returnResult->getWedTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getWedTime() ? clone $returnResult->getWedTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['wedCheck'] = true;
            }
            if (($outwardResult->getThuTime() ? $outwardResult->getThuTime()->format('H:i') : 'null').' '.($returnResult->getThuTime() ? $returnResult->getThuTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getThuTime() ? clone $outwardResult->getThuTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getThuTime() ? clone $outwardResult->getThuTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getThuTime() ? clone $returnResult->getThuTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getThuTime() ? clone $returnResult->getThuTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['thuCheck'] = true;
            }
            if (($outwardResult->getFriTime() ? $outwardResult->getFriTime()->format('H:i') : 'null').' '.($returnResult->getFriTime() ? $returnResult->getFriTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getFriTime() ? clone $outwardResult->getFriTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getFriTime() ? clone $outwardResult->getFriTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getFriTime() ? clone $returnResult->getFriTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getFriTime() ? clone $returnResult->getFriTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['friCheck'] = true;
            }
            if (($outwardResult->getSatTime() ? $outwardResult->getSatTime()->format('H:i') : 'null').' '.($returnResult->getSatTime() ? $returnResult->getSatTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getSatTime() ? clone $outwardResult->getSatTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getSatTime() ? clone $outwardResult->getSatTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getSatTime() ? clone $returnResult->getSatTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getSatTime() ? clone $returnResult->getSatTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['satCheck'] = true;
            }
            if (($outwardResult->getSunTime() ? $outwardResult->getSunTime()->format('H:i') : 'null').' '.($returnResult->getSunTime() ? $returnResult->getSunTime()->format('H:i') : 'null') == $time) {
                // outward
                $outwardDriverDepartureTime = $outwardResult->getSunTime() ? clone $outwardResult->getSunTime() : null;
                $schedule['outwardPickUpTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getPickUpDuration().' seconds') : null;
                $outwardDriverDepartureTime = $outwardResult->getSunTime() ? clone $outwardResult->getSunTime() : null;
                $schedule['outwardDropOffTime'] = $outwardDriverDepartureTime ? $outwardDriverDepartureTime->modify('+'.$askConcerned->getMatching()->getDropOffDuration().' seconds') : null;
                // return
                $returnDriverDepartureTime = $returnResult->getSunTime() ? clone $returnResult->getSunTime() : null;
                $schedule['returnPickUpTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getPickUpDuration().' seconds') : null;
                $returnDriverDepartureTime = $returnResult->getSunTime() ? clone $returnResult->getSunTime() : null;
                $schedule['returnDropOffTime'] = $returnDriverDepartureTime ? $returnDriverDepartureTime->modify('+'.$askConcerned->getAskLinked()->getMatching()->getDropOffDuration().' seconds') : null;
                $schedule['sunCheck'] = true;
            }
            $multipleSchedules[] = $schedule;
            $schedule = [
                'outwardPickUpTime' => null,
                'outwardDropOffTime' => null,
                'returnPickUpTime' => null,
                'returnDropOffTime' => null,
                'monCheck' => false,
                'tueCheck' => false,
                'wedCheck' => false,
                'thuCheck' => false,
                'friCheck' => false,
                'satCheck' => false,
                'sunCheck' => false,
            ];
        }

        return $multipleSchedules;
    }
}
