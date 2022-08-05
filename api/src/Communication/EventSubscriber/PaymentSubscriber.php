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

namespace App\Communication\EventSubscriber;

use App\Carpool\Entity\Ask;
use App\Communication\Service\NotificationManager;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Event\CarpoolItemPersistedEvent;
use App\Payment\Event\ConfirmDirectPaymentEvent;
use App\Payment\Event\ConfirmDirectPaymentRegularEvent;
use App\Payment\Event\IdentityProofAcceptedEvent;
use App\Payment\Event\IdentityProofOutdatedEvent;
use App\Payment\Event\IdentityProofRejectedEvent;
use App\Payment\Event\PayAfterCarpoolEvent;
use App\Payment\Event\PayAfterCarpoolRegularEvent;
use App\Payment\Event\SignalDeptEvent;
use App\User\Event\ConfirmedCarpoolerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
            IdentityProofOutdatedEvent::NAME => 'onIdentityProofOutdated',
            CarpoolItemPersistedEvent::NAME => 'onPersistedCarpoolItem',
        ];
    }

    public function onConfirmDirectPayment(ConfirmDirectPaymentEvent $event)
    {
        $recipient = $event->getCarpoolItem()->getCreditorUser();
        $this->notificationManager->notifies(ConfirmDirectPaymentEvent::NAME, $recipient, $event->getCarpoolItem());
    }

    public function onConfirmDirectPaymentRegular(ConfirmDirectPaymentRegularEvent $event)
    {
        $recipient = $event->getCarpoolItem()->getCreditorUser();
        $this->notificationManager->notifies(ConfirmDirectPaymentRegularEvent::NAME, $recipient, $event->getCarpoolItem());
    }

    public function onPayAfterCarpool(PayAfterCarpoolEvent $event)
    {
        $recipient = $event->getCarpoolItem()->getDebtorUser();
        $this->notificationManager->notifies(PayAfterCarpoolEvent::NAME, $recipient, $event->getCarpoolItem());
    }

    public function onPayAfterCarpoolRegular(PayAfterCarpoolRegularEvent $event)
    {
        $recipient = $event->getCarpoolItem()->getDebtorUser();
        $this->notificationManager->notifies(PayAfterCarpoolRegularEvent::NAME, $recipient, $event->getCarpoolItem());
    }

    public function onSignalDept(SignalDeptEvent $event)
    {
        $recipient = $event->getCarpoolItem()->getDebtorUser();
        $this->notificationManager->notifies(SignalDeptEvent::NAME, $recipient, $event->getCarpoolItem());
    }

    public function onIdentityProofAccepted(IdentityProofAcceptedEvent $event)
    {
        $recipient = $event->getPaymentProfile()->getUser();
        $this->notificationManager->notifies(IdentityProofAcceptedEvent::NAME, $recipient, $event->getPaymentProfile());
    }

    public function onIdentityProofRejected(IdentityProofRejectedEvent $event)
    {
        $recipient = $event->getPaymentProfile()->getUser();
        $this->notificationManager->notifies(IdentityProofRejectedEvent::NAME, $recipient, $event->getPaymentProfile());
    }

    public function onIdentityProofOutdated(IdentityProofOutdatedEvent $event)
    {
        $recipient = $event->getPaymentProfile()->getUser();
        $this->notificationManager->notifies(IdentityProofOutdatedEvent::NAME, $recipient, $event->getPaymentProfile());
    }

    public function onPersistedCarpoolItem(CarpoolItemPersistedEvent $event)
    {
        $creditor = $event->getCarpoolItem()->getCreditorUser();
        // we get all user's asks
        $asks = array_merge($creditor->getAsks(), $creditor->getAsksRelated());
        $carpooledKm = null;
        foreach ($asks as $ask) {
            if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus()) {
                $carpoolItems = $ask->getCarpoolItems();
                $numberOfTravel = null;
                foreach ($carpoolItems as $carpoolItem) {
                    if (CarpoolItem::STATUS_REALIZED == $carpoolItem->getItemStatus()) {
                        $numberOfTravel = +1;
                    }
                }
                $carpooledKm = $carpooledKm + ($ask->getMatching()->getCommonDistance() * $numberOfTravel);
            }
        }
        if ($carpooledKm / 1000 > 1000) {
            $event = new ConfirmedCarpoolerEvent($creditor);
            $this->eventDispatcher->dispatch(ConfirmedCarpoolerEvent::NAME, $event);
        }

        $debtor = $event->getCarpoolItem()->getDebtorUser();
        // we get all user's asks
        $asks = array_merge($debtor->getAsks(), $debtor->getAsksRelated());
        $carpooledKm = null;
        foreach ($asks as $ask) {
            if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus()) {
                $carpoolItems = $ask->getCarpoolItems();
                $numberOfTravel = null;
                foreach ($carpoolItems as $carpoolItem) {
                    if (CarpoolItem::STATUS_REALIZED == $carpoolItem->getItemStatus()) {
                        $numberOfTravel = +1;
                    }
                }
                $carpooledKm = $carpooledKm + ($ask->getMatching()->getCommonDistance() * $numberOfTravel);
            }
        }
        if ($carpooledKm / 1000 > 1000) {
            $event = new ConfirmedCarpoolerEvent($debtor);
            $this->eventDispatcher->dispatch(ConfirmedCarpoolerEvent::NAME, $event);
        }
    }
}
