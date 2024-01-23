<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

use App\Action\Repository\ActionRepository;
use App\Carpool\Event\CarpoolProofCertifyDropOffEvent;
use App\Carpool\Event\CarpoolProofCertifyPickUpEvent;
use App\Communication\Service\NotificationManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CarpoolproofSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $eventDispatcher;
    private $actionRepository;

    public function __construct(NotificationManager $notificationManager, EventDispatcherInterface $eventDispatcher, ActionRepository $actionRepository)
    {
        $this->notificationManager = $notificationManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->actionRepository = $actionRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofCertifyPickUpEvent::NAME => 'onCertifyPickUp',
            CarpoolProofCertifyDropOffEvent::NAME => 'onCertifyDropOff',
        ];
    }

    public function onCertifyPickUp(CarpoolProofCertifyPickUpEvent $event)
    {
        $this->notificationManager->notifies(CarpoolProofCertifyPickUpEvent::NAME, $event->getRecipient(), $event->getCarpoolProof());
    }

    public function onCertifyDropOff(CarpoolProofCertifyDropOffEvent $event)
    {
        $this->notificationManager->notifies(CarpoolProofCertifyDropOffEvent::NAME, $event->getRecipient(), $event->getCarpoolProof());
    }
}
