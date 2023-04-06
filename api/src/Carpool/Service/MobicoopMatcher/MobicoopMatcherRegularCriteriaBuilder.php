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
class MobicoopMatcherRegularCriteriaBuilder
{
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    /**
     * @var Criteria
     */
    private $_criteria;

    private $_journeys;
    private $_searchProposal;

    public function __construct(Criteria $criteria, array $result, Proposal $searchProposal)
    {
        $this->_criteria = $criteria;
        $this->_journeys = $result['journeys'];
        $this->_searchProposal = $searchProposal;
    }

    public function build(): Criteria
    {
        $this->_setDates();
        $this->_setTimes();
        $this->_setDays();
        $this->_setMargins();
        $this->_setMinAndMaxTime();

        return $this->_criteria;
    }

    private function _setDates()
    {
        $fromDate = new \DateTime();
        $toDate = \DateTime::createFromFormat(self::DATE_FORMAT, '1960-01-01');
        foreach ($this->_journeys as $journey) {
            $firstDate = \DateTime::createFromFormat(self::DATE_FORMAT, $journey['first_date']);
            $lastDate = \DateTime::createFromFormat(self::DATE_FORMAT, $journey['last_date']);
            if ($firstDate < $fromDate) {
                $fromDate = $firstDate;
            }
            if ($lastDate > $toDate) {
                $toDate = $lastDate;
            }
        }

        $this->_criteria->setFromDate($fromDate);
        $this->_criteria->setToDate($toDate);
    }

    private function _setTimes()
    {
        foreach ($this->_journeys as $journey) {
            $setter = 'set'.ucfirst($journey['weekday']).'Time';
            $this->_criteria->{$setter}(\DateTime::createFromFormat(self::TIME_FORMAT, $journey['waypoints'][0]['time']));
        }
    }

    private function _initDays()
    {
        foreach (Criteria::DAYS as $day) {
            $this->_checkADay($day, false);
        }
    }

    private function _checkADay(string $day, bool $check = true)
    {
        $setter = 'set'.ucfirst($day).'Check';
        $this->_criteria->{$setter}($check);
    }

    private function _setDays()
    {
        $this->_initDays();
        foreach ($this->_journeys as $journey) {
            $this->_checkADay($journey['weekday']);
        }
    }

    private function _setMargins()
    {
        foreach (Criteria::DAYS as $day) {
            if (Criteria::FREQUENCY_PUNCTUAL == $this->_searchProposal->getCriteria()->getFrequency()) {
                // traitement ponctuel
                $setter = 'set'.$day.'MarginDuration';
                $this->_criteria->{$setter}($this->_searchProposal->getCriteria()->getMarginDuration());
            } else {
                $getter = 'get'.ucfirst($day).'MarginDuration';
                $setter = 'set'.ucfirst($day).'MarginDuration';
                $this->_criteria->{$setter}($this->_searchProposal->getCriteria()->{$getter}());
            }
        }
    }

    private function _setMinAndMaxTime()
    {
        foreach (Criteria::DAYS as $day) {
            $getterTime = 'get'.ucfirst($day).'Time';
            $getterMargin = 'get'.ucfirst($day).'MarginDuration';
            $setter = 'set'.ucfirst($day).'MinTime';
            if (!is_null($this->_criteria->{$getterTime}())) {
                $minTime = clone $this->_criteria->{$getterTime}();
                $this->_criteria->{$setter}($minTime->modify('-'.$this->_criteria->{$getterMargin}().' seconds'));
                $maxTime = clone $this->_criteria->{$getterTime}();
                $this->_criteria->{$setter}($maxTime->modify('+'.$this->_criteria->{$getterMargin}().' seconds'));
            }
        }
    }
}
