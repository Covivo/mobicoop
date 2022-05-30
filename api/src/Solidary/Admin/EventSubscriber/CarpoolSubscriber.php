<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Admin\EventSubscriber;

use App\Carpool\Event\MatchingNewEvent;
use App\Solidary\Admin\Service\SolidaryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for carpool related events
 */
class CarpoolSubscriber implements EventSubscriberInterface
{
    private $solidaryManager;

    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            MatchingNewEvent::NAME => 'onNewMatching',
        ];
    }
        
    /**
     * Executed when a new matching is discovered : check if the proposalRequest is a Solidary proposal.
     * If so, create the related SolidaryMatching.
     *
     * @param MatchingNewEvent $event   The event
     * @return void
     */
    public function onNewMatching(MatchingNewEvent $event): void
    {
        // check if the request proposal is related to a SolidaryRecord
        // we also check the potential linked proposal because only the outward proposal is related to the SolidaryRecord
        if ($event->getMatching()->getProposalRequest()->getSolidary()) {
            $this->solidaryManager->createSolidaryMatchingFromCarpoolMatching($event->getMatching(), $event->getMatching()->getProposalRequest()->getSolidary());
        } elseif ($event->getMatching()->getProposalRequest()->getProposalLinked() && $event->getMatching()->getProposalRequest()->getProposalLinked()->getSolidary()) {
            $this->solidaryManager->createSolidaryMatchingFromCarpoolMatching($event->getMatching(), $event->getMatching()->getProposalRequest()->getProposalLinked()->getSolidary());
        }
    }
}
