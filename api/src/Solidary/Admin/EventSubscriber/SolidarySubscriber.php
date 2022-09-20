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
 */

namespace App\Solidary\Admin\EventSubscriber;

use App\Solidary\Admin\Event\BeneficiaryStatusChangedEvent;
use App\Solidary\Admin\Event\VolunteerStatusChangedEvent;
use App\Solidary\Admin\Service\SolidaryBeneficiaryManager;
use App\Solidary\Admin\Service\SolidaryTransportMatcher;
use App\Solidary\Admin\Service\SolidaryVolunteerManager;
use App\Solidary\Entity\SolidaryUserStructure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for solidary related events.
 */
class SolidarySubscriber implements EventSubscriberInterface
{
    private $solidaryTransportMatcher;
    private $solidaryVolunteerManager;
    private $solidaryBeneficiaryManager;

    public function __construct(SolidaryTransportMatcher $solidaryTransportMatcher, SolidaryVolunteerManager $solidaryVolunteerManager, SolidaryBeneficiaryManager $solidaryBeneficiaryManager)
    {
        $this->solidaryTransportMatcher = $solidaryTransportMatcher;
        $this->solidaryVolunteerManager = $solidaryVolunteerManager;
        $this->solidaryBeneficiaryManager = $solidaryBeneficiaryManager;
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
     * @param VolunteerStatusChangedEvent $event The event
     */
    public function onVolunteerStatusChanged(VolunteerStatusChangedEvent $event)
    {
        if (SolidaryUserStructure::STATUS_ACCEPTED == $event->getSolidaryUserStructure()->getStatus()) {
            $this->solidaryTransportMatcher->matchForStructure($event->getSolidaryUserStructure()->getStructure());
        }
        $this->solidaryVolunteerManager->checkIsVolunteer($event->getSolidaryUserStructure());
    }

    /**
     * Executed when a beneficiary status is changed within a Structure.
     *
     * @param BeneficiaryStatusChangedEvent $event The event
     */
    public function onBeneficiaryStatusChanged(BeneficiaryStatusChangedEvent $event)
    {
        $this->solidaryBeneficiaryManager->checkIsBeneficiary($event->getSolidaryUserStructure());
    }
}
