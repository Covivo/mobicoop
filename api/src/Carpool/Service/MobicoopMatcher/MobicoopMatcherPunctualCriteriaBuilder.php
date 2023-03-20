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
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Service\FormatDataManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherPunctualCriteriaBuilder
{
    public const ROLE_DRIVER = 'driver';
    public const ROLE_PASSENGER = 'passenger';

    public const STEP_START = 'start';
    public const STEP_FINISH = 'finish';

    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    public const DEFAULT_SEATS_DRIVER = 3;
    public const DEFAULT_SEATS_PASSENGER = 1;

    /**
     * @var Criteria
     */
    private $_criteria;

    private $_searchProposal;
    private $_role;
    private $_seats;
    private $_journey;
    private $_currentMatching;

    public function __construct(Criteria $criteria, array $result, Proposal $searchProposal, Matching $currentMatching)
    {
        $this->_criteria = $criteria;
        $this->_role = $result['role'];
        $this->_seats = $result['seats'];
        $this->_journey = $result['journeys'][0];
        $this->_searchProposal = $searchProposal;
        $this->_currentMatching = $currentMatching;
    }

    public function build(): Criteria
    {
        $this->_setDateAndTime();
        $this->_setMarginMinAndMaxTime();
        $this->_setSeats();
        $this->_setModes();
        $this->_setPrices();

        return $this->_criteria;
    }

    private function _setDateAndTime()
    {
        $this->_criteria->setFromDate(\DateTime::createFromFormat(self::DATE_FORMAT, $this->_journey['first_date']));
        $waypoint = $this->_findFirstWaypoint($this->_journey['waypoints']);
        $this->_criteria->setFromTime(\DateTime::createFromFormat(self::TIME_FORMAT, $waypoint['time']));

        $this->_criteria->setStrictDate($this->_searchProposal->getCriteria()->isStrictDate());
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
        $minTime = clone $this->_criteria->getFromTime();
        $this->_criteria->setMinTime($minTime->modify('-'.$this->_criteria->getMarginDuration().' seconds'));
        $maxTime = clone $this->_criteria->getFromTime();
        $this->_criteria->setMaxTime($maxTime->modify('+'.$this->_criteria->getMarginDuration().' seconds'));
    }

    private function _setSeats()
    {
        $this->_criteria->setSeatsDriver(self::DEFAULT_SEATS_DRIVER);
        $this->_criteria->setSeatsPassenger(self::DEFAULT_SEATS_PASSENGER);
        if (self::ROLE_DRIVER == $this->_role) {
            $this->_criteria->setSeatsDriver($this->_seats);
        } elseif (self::ROLE_PASSENGER == $this->_role) {
            $this->_criteria->setSeatsPassenger($this->_seats);
        }
    }

    private function _setModes()
    {
        $this->_criteria->setAnyRouteAsPassenger($this->_searchProposal->getCriteria()->getAnyRouteAsPassenger());
        $this->_criteria->setMultiTransportMode($this->_searchProposal->getCriteria()->getMultiTransportMode());
    }

    private function _setPrices()
    {
        $formatDataManager = new FormatDataManager();
        $this->_criteria->setDriverComputedPrice(($this->_currentMatching->getCommonDistance() + $this->_currentMatching->getDetourDistance()) * $this->_currentMatching->getProposalOffer()->getCriteria()->getPriceKm() / 1000);
        $this->_criteria->setDriverComputedRoundedPrice($formatDataManager->roundPrice((float) $this->_criteria->getDriverComputedPrice(), $this->_criteria->getFrequency()));
        $this->_criteria->setPassengerComputedPrice($this->_criteria->getDriverComputedPrice());
        $this->_criteria->setPassengerComputedRoundedPrice($this->_criteria->getDriverComputedRoundedPrice());
    }
}
