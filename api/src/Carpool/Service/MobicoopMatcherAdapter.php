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
use App\Carpool\Entity\MobicoopMatcher\Search;
use App\Carpool\Entity\MobicoopMatcher\Waypoint;
use App\Carpool\Entity\Proposal;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherAdapter
{
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i';

    /**
     * @var Proposal
     */
    private $_proposal;

    /**
     * @var Search
     */
    private $_search;

    public function buildSearchFromProposal(Proposal $proposal): Search
    {
        $this->_proposal = $proposal;

        $this->_search = new Search();

        $this->_treatStartDate();
        $this->_treatWaypoints();
        $this->_treatRole();

        return $this->_search;
    }

    private function _treatStartDate()
    {
        if (Criteria::FREQUENCY_PUNCTUAL == $this->_proposal->getCriteria()->getFrequency()) {
            $departure = $this->_proposal->getCriteria()->getFromDate()->format(self::DATE_FORMAT).' '.$this->_proposal->getCriteria()->getFromTime()->format(self::TIME_FORMAT);
            $this->_search->setDeparture($departure);
        } else {
            $this->_search->setFromDate($this->_proposal->getCriteria()->getFromDate()->format(self::DATE_FORMAT));
        }
    }

    private function _treatWaypoints()
    {
        $waypoints = [];
        foreach ($this->_proposal->getWaypoints() as $proposalWaypoint) {
            $waypoint = new Waypoint();
            $waypoint->setLat($proposalWaypoint->getAddress()->getLatitude());
            $waypoint->setLon($proposalWaypoint->getAddress()->getLongitude());
            $waypoints[] = $waypoint;
        }

        $this->_search->setWaypoints($waypoints);
    }

    private function _treatRole()
    {
        if ($this->_proposal->getCriteria()->isDriver()) {
            $this->_search->setDriver(true);
        }
        if ($this->_proposal->getCriteria()->isPassenger()) {
            $this->_search->setPassenger(true);
        }
    }
}
