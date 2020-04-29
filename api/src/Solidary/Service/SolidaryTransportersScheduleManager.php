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

namespace App\Solidary\Service;

use App\Carpool\Entity\Criteria;
use App\Solidary\Entity\SolidaryTransportersSchedule\SolidaryTransportersSchedule;
use App\Solidary\Entity\SolidaryTransportersSchedule\SolidaryTransportersScheduleItem;
use App\Solidary\Repository\SolidaryAskRepository;
use App\Solidary\Entity\SolidaryAsk;

/**
 * Solidary transporters schedule manager service
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryTransportersScheduleManager
{
    private $solidaryAskRepository;
    private $solidaryMatcher;

    public function __construct(SolidaryAskRepository $solidaryAskRepository, SolidaryMatcher $solidaryMatcher)
    {
        $this->solidaryAskRepository = $solidaryAskRepository;
        $this->solidaryMatcher = $solidaryMatcher;
    }

    public function buildSolidaryTransportersSchedule(SolidaryTransportersSchedule $schedule): SolidaryTransportersSchedule
    {
        // If no start date, we take today and set enddate to +1 week
        // If no end date, we take startDate +1 week
        if (is_null($schedule->getStartDate())) {
            $schedule->setStartDate(new \DateTime());
        }
        if (is_null($schedule->getEndDate())) {
            $startDate = clone $schedule->getStartDate();
            $schedule->setEndDate($startDate->modify('+1 week'));
        }

        $solidaryAsks = $this->solidaryAskRepository->findBetweenTwoDates($schedule->getStartDate(), $schedule->getEndDate());
        
        $scheduleAsks = [];
        $currentDate = clone $schedule->getStartDate();
        // We make the schedule. Day by day.
        while ($currentDate<=$schedule->getEndDate()) {
            $item = new SolidaryTransportersScheduleItem();
            $item->setDate($currentDate);
            
            // We check if we found a solidaryAsk for this day
            foreach ($solidaryAsks as $solidaryAsk) {
                /**
                 * @var SolidaryAsk $solidaryAsk
                 */
                if (
                   ($solidaryAsk->getCriteria()->getFrequency()==Criteria::FREQUENCY_PUNCTUAL && $solidaryAsk->getCriteria()->getFromDate()->format("d/m/Y")==$currentDate->format("d/m/Y")) ||
                   ($solidaryAsk->getCriteria()->getFrequency()==Criteria::FREQUENCY_REGULAR && $solidaryAsk->getCriteria()->getFromDate()->format("d/m/Y")<=$currentDate->format("d/m/Y") && $solidaryAsk->getCriteria()->getToDate()->format("d/m/Y")>=$currentDate->format("d/m/Y"))
                ) {
                    $volunteer = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
                    $item->setVolunteer($volunteer->getGivenName()." ".$volunteer->getFamilyName());

                    // Determine the hour slot
                    $structure = $solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getStructure();
                    $item->setSlot($this->solidaryMatcher->getHourSlot($solidaryAsk->getCriteria()->getFromTime(), $solidaryAsk->getCriteria()->getFromTime(), $structure));

                    // different usefull ids
                    $item->setSolidaryId($solidaryAsk->getSolidarySolution()->getSolidary()->getId());
                    $item->setSolidarySolutionId($solidaryAsk->getSolidarySolution()->getId());

                    // status
                    $item->setStatus($solidaryAsk->getStatus());

                    break;
                }
            }

            $scheduleAsks[] = $item;
            
            $currentDate = $currentDate->modify('+1 day');
        }

        $schedule->setSchedule($scheduleAsks);

        return $schedule;
    }
}
