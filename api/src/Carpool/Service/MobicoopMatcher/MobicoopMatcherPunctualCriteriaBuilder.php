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

namespace App\Carpool\Service\MobicoopMatcher;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherPunctualCriteriaBuilder
{
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    /**
     * @var Criteria
     */
    private $_criteria;

    private $_searchProposal;
    private $_journey;

    public function __construct(Criteria $criteria, array $result, Proposal $searchProposal)
    {
        $this->_criteria = $criteria;
        $this->_journey = $result['journeys'][0];
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

        $waypointExtractor = new MobicoopMatcherWaypointExtractor($this->_journey['waypoints']);
        $waypoint = $waypointExtractor->findFirstWaypoint();
        $this->_criteria->setFromTime(\DateTime::createFromFormat(self::TIME_FORMAT, $waypoint['time']));

        $this->_criteria->setStrictDate($this->_searchProposal->getCriteria()->isStrictDate());
    }

    private function _setMarginMinAndMaxTime()
    {
        $this->_criteria->setMarginDuration($this->_getMarginDurationOfSearch());
        $minTime = clone $this->_criteria->getFromTime();
        $this->_criteria->setMinTime($minTime->modify('-'.$this->_criteria->getMarginDuration().' seconds'));
        $maxTime = clone $this->_criteria->getFromTime();
        $this->_criteria->setMaxTime($maxTime->modify('+'.$this->_criteria->getMarginDuration().' seconds'));
    }

    private function _getMarginDurationOfSearch(): ?int
    {
        if (Criteria::FREQUENCY_PUNCTUAL == $this->_searchProposal->getCriteria()->getFrequency()) {
            return $this->_searchProposal->getCriteria()->getMarginDuration();
        }
        $marginGetter = 'get'.Criteria::DAYS[$this->_criteria->getFromDate()->format('N') - 1].'MarginDuration';

        return $this->_searchProposal->getCriteria()->{$marginGetter}();
    }
}
