<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Carpool\EventSubscriber;

use App\Carpool\Event\CarpoolProofCreatedEvent;
use App\Carpool\Service\ProofManager;
use App\ExternalService\Interfaces\SendProof as ExternalServiceSendProof;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CarpoolProofEventSubscriber implements EventSubscriberInterface
{
    private $_externalServiceSendProof;
    private $_proofManager;
    private $_externalServiceEnable;

    public function __construct(ProofManager $proofManager, ExternalServiceSendProof $externalServiceSendProof, bool $externalServiceEnable)
    {
        $this->_externalServiceSendProof = $externalServiceSendProof;
        $this->_proofManager = $proofManager;
        $this->_externalServiceEnable = $externalServiceEnable;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofCreatedEvent::NAME => 'onCarpoolProofCreated',
        ];
    }

    /**
     * @throws ClassNotFoundException
     */
    public function onCarpoolProofCreated(CarpoolProofCreatedEvent $event)
    {
        if ($this->_externalServiceEnable) {
            $this->_externalServiceSendProof->send($this->_proofManager->buildCarpoolProofDto($event->getCarpoolProof()));
        }
    }
}
