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

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherCriteriaBuilder
{
    public const ROLE_DRIVER = 'driver';
    public const ROLE_PASSENGER = 'passenger';
    public const STEP_START = 'start';
    public const STEP_FINISH = 'finish';

    /**
     * @var Criteria
     */
    private $_criteria;

    private $_searchProposal;
    private $_result;

    public function build(Proposal $searchProposal, array $result, Matching $currentMatching): Criteria
    {
        $this->_searchProposal = $searchProposal;
        $this->_result = $result;

        $this->_criteria = new Criteria();

        $this->_setRoles();
        $this->_setFrequency();

        if (Criteria::FREQUENCY_PUNCTUAL == $this->_criteria->getFrequency()) {
            // punctual
            $punctualCriteriaBuilder = new MobicoopMatcherPunctualCriteriaBuilder($this->_criteria, $result, $this->_searchProposal, $currentMatching);
            $this->_criteria = $punctualCriteriaBuilder->build();
        }

        // TO DO : regular

        return $this->_criteria;
    }

    private function _setRoles()
    {
        if (self::ROLE_DRIVER == $this->_result['role']) {
            $this->_criteria->setDriver(true);
            $this->_criteria->setPassenger(false);
        }
        if (self::ROLE_PASSENGER == $this->_result['role']) {
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
}
