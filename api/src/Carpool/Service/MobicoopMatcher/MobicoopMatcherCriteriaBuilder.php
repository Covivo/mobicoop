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
use App\Carpool\Entity\MobicoopMatcher\Waypoint;
use App\Carpool\Entity\Proposal;
use App\Service\FormatDataManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherCriteriaBuilder
{
    public const DEFAULT_SEATS_DRIVER = 3;
    public const DEFAULT_SEATS_PASSENGER = 1;

    /**
     * @var Criteria
     */
    private $_criteria;

    /**
     * @var Matching
     */
    private $_currentMatching;
    private $_role;
    private $_seats;

    private $_searchProposal;
    private $_result;

    public function build(Proposal $searchProposal, array $result, Matching $currentMatching): Criteria
    {
        $this->_searchProposal = $searchProposal;
        $this->_currentMatching = $currentMatching;
        $this->_role = $result['role'];
        $this->_seats = $result['seats'];

        $this->_result = $result;

        $this->_criteria = new Criteria();

        $this->_setRoles();
        $this->_setFrequency();
        $this->_setSeats();
        $this->_setModes();
        $this->_setPrices();

        if (Criteria::FREQUENCY_PUNCTUAL == $this->_criteria->getFrequency()) {
            // punctual
            $punctualCriteriaBuilder = new MobicoopMatcherPunctualCriteriaBuilder($this->_criteria, $result, $this->_searchProposal);
            $this->_criteria = $punctualCriteriaBuilder->build();
        } else {
            $regularCriteriaBuilder = new MobicoopMatcherRegularCriteriaBuilder($this->_criteria, $result, $this->_searchProposal);
            $this->_criteria = $regularCriteriaBuilder->build();
        }

        return $this->_criteria;
    }

    private function _setRoles()
    {
        if (Waypoint::ROLE_DRIVER == $this->_result['role']) {
            $this->_criteria->setDriver(true);
            $this->_criteria->setPassenger(false);
        }
        if (Waypoint::ROLE_PASSENGER == $this->_result['role']) {
            $this->_criteria->setDriver(false);
            $this->_criteria->setPassenger(true);
        }
    }

    private function _setFrequency()
    {
        if ($this->_result['journeys'][0]['first_date'] === $this->_result['journeys'][0]['last_date']) {
            $this->_criteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        } else {
            $this->_criteria->setFrequency(Criteria::FREQUENCY_REGULAR);
        }
    }

    private function _setSeats()
    {
        $this->_criteria->setSeatsDriver(self::DEFAULT_SEATS_DRIVER);
        $this->_criteria->setSeatsPassenger(self::DEFAULT_SEATS_PASSENGER);
        if (Waypoint::ROLE_DRIVER == $this->_role) {
            $this->_criteria->setSeatsDriver($this->_seats);
        } elseif (Waypoint::ROLE_PASSENGER == $this->_role) {
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
