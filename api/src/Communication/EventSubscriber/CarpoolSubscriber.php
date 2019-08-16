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
use App\Carpool\Event\AdRenewalEvent;
use App\Carpool\Event\AskAcceptedEvent;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskRefusedEvent;
use App\Carpool\Event\AskUpdatedEvent;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Event\ProposalPostedEvent;
use App\Carpool\Repository\AskHistoryRepository;
use App\Communication\Service\NotificationManager;
use App\TranslatorTrait;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CarpoolSubscriber implements EventSubscriberInterface
{
    use TranslatorTrait;
    
    private $notificationManager;
    private $askHistoryRepository;
    const DYNAMIC_EVENT_NAMESPACE='App\Carpool\Event';
    
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
            ProposalPostedEvent::NAME => 'onProposalPosted',
            AskUpdatedEvent::NAME => 'onAskUpdated'
        ];
    }
    
    /**
     * Executed when a new ask is posted
     *
     * @param AskPostedEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onAskPosted(AskPostedEvent $event)
    {
        $askType=($event->getAsk()->getMatching()->getProposalOffer()->getCriteria()->getFrequency()==1)?'Punctual':'Regular';
        $askUser=($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId())?'Driver':'Passenger';
        $class= self::DYNAMIC_EVENT_NAMESPACE.'\AskPosted'.$askUser.$askType.'Event';
        if(!class_exists($class)) throw new ClassNotFoundException($this->translator->trans('Class %class% not found', ['%class%'=> $class]));
        $this->notificationManager->notifies($class::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $event->getAsk()->getAskHistories()[0]);
    }
    
    /**
     * Executed when an ask is accepted
     *
     * @param AskAcceptedEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onAskAccepted(AskAcceptedEvent $event)
    {
        // we must notify the recipient of the ask, the message is related to the last accepted status of the ask history
        $lastAskHistory = $this->askHistoryRepository->findLastByAskAndstatus($event->getAsk(), Ask::STATUS_ACCEPTED);
        $askType=($event->getAsk()->getMatching()->getProposalOffer()->getCriteria()->getFrequency()==1)?'Punctual':'Regular';
        $askUser=($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId())?'Driver':'Passenger';
        $class= self::DYNAMIC_EVENT_NAMESPACE.'\AskAccepted'.$askUser.$askType.'Event';
        if(!class_exists($class)) throw new ClassNotFoundException($this->translator->trans('Class %class% not found', ['%class%'=> $class]));
        $this->notificationManager->notifies($class::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $lastAskHistory);
    }
    
    /**
     * Executed when an ask is declined
     *
     * @param AskRefusedEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onAskRefused(AskRefusedEvent $event)
    {
        // we must notify the recipient of the ask, the message is related to the last refused status of the ask history
        $lastAskHistory = $this->askHistoryRepository->findLastByAskAndstatus($event->getAsk(), Ask::STATUS_DECLINED);
        $askType=($event->getAsk()->getMatching()->getProposalOffer()->getCriteria()->getFrequency()==1)?'Punctual':'Regular';
        $askUser=($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId())?'Driver':'Passenger';
        $class= self::DYNAMIC_EVENT_NAMESPACE.'\AskRefused'.$askUser.$askType.'Event';
        if(!class_exists($class)) throw new ClassNotFoundException($this->translator->trans('Class %class% not found', ['%class%'=> $class]));
        $this->notificationManager->notifies($class::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $lastAskHistory);
    }
    
    /**
     * Executed when Ask is updated
     *
     * @param AskUpdatedEvent $event
     * @throws ClassNotFoundException
     */
    public function onAskUpdated(AskUpdatedEvent $event)
    {
        $lastAskHistory = $this->askHistoryRepository->findLastByAskAndstatus($event->getAsk(), Ask::STATUS_PENDING);
        $askType=($event->getAsk()->getMatching()->getProposalOffer()->getCriteria()->getFrequency()==1)?'Punctual':'Regular';
        $askUser=($event->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() != $event->getAsk()->getId())?'Driver':'Passenger';
        $class= self::DYNAMIC_EVENT_NAMESPACE.'\AskUpdated'.$askUser.$askType.'Event';
        if(!class_exists($class)) throw new ClassNotFoundException($this->translator->trans('Class %class% not found', ['%class%'=> $class]));
        $this->notificationManager->notifies($class::NAME, $event->getAsk()->getMatching()->getProposalRequest()->getUser(), $lastAskHistory);
    }
    
    /**
     * Executed when a new matching is discovered
     *
     * @param MatchingNewEvent $event
     * @return void
     * @throws ClassNotFoundException
     */
    public function onNewMatching(MatchingNewEvent $event)
    {
        $askType=($event->getMatching()->getProposalOffer()->getCriteria()->getFrequency()==1)?'Punctual':'Regular';
        $askUser=($event->getMatching()->getProposalOffer()->getCreatedDate() < $event->getMatching()->getProposalRequest()->getCreatedDate())?'Driver':'Passenger';
        $class= self::DYNAMIC_EVENT_NAMESPACE.'\MatchingNew'.$askUser.$askType.'Event';
        if(!class_exists($class)) throw new ClassNotFoundException($this->translator->trans('Class %class% not found', ['%class%'=> $class]));
        $proposalObject= ($askUser=='Driver')? $event->getMatching()->getProposalOffer()->getUser(): $event->getMatching()->getProposalRequest()->getUser();
        $this->notificationManager->notifies($class::NAME, $proposalObject, $event->getMatching());
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
