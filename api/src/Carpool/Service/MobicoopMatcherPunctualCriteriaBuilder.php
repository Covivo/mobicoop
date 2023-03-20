<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherPunctualCriteriaBuilder
{
    public const ROLE_DRIVER = 'driver';
    public const ROLE_PASSENGER = 'passenger';
    public const STEP_START = 'start';
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    /**
     * @var Criteria
     */
    private $_criteria;

    private $_searchProposal;
    private $_journey;

    public function __construct(Criteria $criteria, array $journey, Proposal $searchProposal)
    {
        $this->_criteria = $criteria;
        $this->_journey = $journey;
        $this->_searchProposal = $searchProposal;
    }

    public function build(): Criteria
    {
        $this->_setDateAndTime();
        $this->_setMarginMinAndMaxTime();

        return $this->_criteria;
    }

    private function _setDateAndTime()
    {
        $this->_criteria->setFromDate(\DateTime::createFromFormat(self::DATE_FORMAT, $this->_journey['first_date']));
        $waypoint = $this->_findFirstWaypoint($this->_journey['waypoints']);
        $this->_criteria->setFromTime(\DateTime::createFromFormat(self::TIME_FORMAT, $waypoint['time']));
    }

    private function _findFirstWaypoint($waypoints): array
    {
        foreach ($waypoints as $waypoint) {
            foreach ($waypoint['actors'] as $actor) {
                if (self::ROLE_DRIVER == $actor['role'] && self::STEP_START == $actor['step']) {
                    return $waypoint;
                }
            }
        }
    }

    private function _setMarginMinAndMaxTime()
    {
        $this->_criteria->setMarginDuration($this->_searchProposal->getCriteria()->getMarginDuration());
    }
}
