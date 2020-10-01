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
 **************************/

namespace App\Communication\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Communication\Service\NotificationManager;
use App\Payment\Event\ConfirmDirectPaymentEvent;
use App\Payment\Event\ConfirmDirectPaymentRegularEvent;
use App\Payment\Event\IdentityProofAcceptedEvent;
use App\Payment\Event\IdentityProofRejectedEvent;
use App\Payment\Event\IdentityProofOutdatedEvent;
use App\Payment\Event\PayAfterCarpoolEvent;
use App\Payment\Event\PayAfterCarpoolRegularEvent;
use App\Payment\Event\SignalDeptEvent;

class PaymentSubscriber implements EventSubscriberInterface
{
    private $notificationManager;


    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConfirmDirectPaymentEvent::NAME => 'onConfirmDirectPayment',
            ConfirmDirectPaymentRegularEvent::NAME => 'onConfirmDirectPaymentRegular',
            PayAfterCarpoolEvent::NAME => 'onPayAfterCarpool',
            PayAfterCarpoolRegularEvent::NAME => 'onPayAfterCarpoolRegular',
            SignalDeptEvent::NAME => 'onSignalDept',
            IdentityProofAcceptedEvent::NAME => 'onIdentityProofAccepted',
            IdentityProofRejectedEvent::NAME => 'onIdentityProofRejected',
            IdentityProofOutdatedEvent::NAME => 'onIdentityProofOutdated'
        ];
    }


   
    public function onConfirmDirectPayment(ConfirmDirectPaymentEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(ConfirmDirectPaymentEvent::NAME, $sender, $event->getCarpoolItem());
    }

   
    public function onConfirmDirectPaymentRegular(ConfirmDirectPaymentRegularEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(ConfirmDirectPaymentRegularEvent::NAME, $sender, $event->getCarpoolItem());
    }

   
    public function onPayAfterCarpool(PayAfterCarpoolEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(PayAfterCarpoolEvent::NAME, $sender, $event->getCarpoolItem());
    }

   
    public function onPayAfterCarpoolRegular(PayAfterCarpoolRegularEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(PayAfterCarpoolRegularEvent::NAME, $sender, $event->getCarpoolItem());
    }

   
    public function onSignalDept(SignalDeptEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(SignalDeptEvent::NAME, $sender, $event->getCarpoolItem());
    }

   
    public function onIdentityProofAccepted(IdentityProofAcceptedEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(IdentityProofAcceptedEvent::NAME, $sender);
    }

   
    public function onIdentityProofRejected(IdentityProofRejectedEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(IdentityProofRejectedEvent::NAME, $sender);
    }

   
    public function onIdentityProofOutdated(IdentityProofOutdatedEvent $event)
    {
        // the recipient is the creator of community
        $sender = ($event->getSender());

        // we must notify the creator of the community
        $this->notificationManager->notifies(IdentityProofOutdatedEvent::NAME, $sender);
    }
}
