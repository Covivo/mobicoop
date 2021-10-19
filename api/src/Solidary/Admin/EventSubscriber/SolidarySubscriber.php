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

use App\Solidary\Admin\Event\BeneficiaryStatusChangedEvent;
use App\Solidary\Admin\Service\SolidaryTransportMatcher;
use App\Solidary\Admin\Service\SolidaryCarpoolMatcher;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Admin\Event\VolunteerStatusChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for solidary related events
 */
class SolidarySubscriber implements EventSubscriberInterface
{
    private $solidaryTransportMatcher;
    private $solidaryCarpoolMatcher;

    public function __construct(SolidaryTransportMatcher $solidaryTransportMatcher, SolidaryCarpoolMatcher $solidaryCarpoolMatcher)
    {
        $this->solidaryTransportMatcher = $solidaryTransportMatcher;
        $this->solidaryCarpoolMatcher = $solidaryCarpoolMatcher;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            VolunteerStatusChangedEvent::NAME => 'onVolunteerStatusChanged',
            BeneficiaryStatusChangedEvent::NAME => 'onBeneficiaryStatusChanged',
        ];
    }
        
    /**
     * Executed when a volunteer status is changed within a Structure.
     *
     * @param VolunteerStatusChangedEvent $event   The event
     * @return void
     */
    public function onVolunteerStatusChanged(VolunteerStatusChangedEvent $event)
    {
        if ($event->getSolidaryUserStructure()->getStatus() == SolidaryUserStructure::STATUS_ACCEPTED) {
            $this->solidaryTransportMatcher->matchForStructure($event->getSolidaryUserStructure()->getStructure());
        }
    }

    /**
     * Executed when a beneficiary status is changed within a Structure.
     *
     * @param BeneficiaryStatusChangedEvent $event   The event
     * @return void
     */
    public function onBeneficiaryStatusChanged(BeneficiaryStatusChangedEvent $event)
    {
        if ($event->getSolidaryUserStructure()->getStatus() == SolidaryUserStructure::STATUS_ACCEPTED) {
            $this->solidaryCarpoolMatcher->createSolidaryMatching($event->getSolidaryUserStructure());
            $this->solidaryTransportMatcher->matchForStructure($event->getSolidaryUserStructure()->getStructure());
        }
    }
}
