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

use App\Solidary\Entity\SolidaryTransportersSchedule\SolidaryTransportersSchedule;
use App\Solidary\Repository\SolidaryAskRepository;

class SolidaryTransportersScheduleManager
{
    private $solidaryAskRepository;

    public function __construct(SolidaryAskRepository $solidaryAskRepository)
    {
        $this->solidaryAskRepository = $solidaryAskRepository;
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
        
        $schedule->setSchedule($solidaryAsks);

        return $schedule;
    }
}
