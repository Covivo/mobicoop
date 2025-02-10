<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\Rdex\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Ressource\Ad;
use App\Rdex\Entity\RdexAddress;
use App\Rdex\Entity\RdexDay;
use App\Rdex\Entity\RdexDayTime;
use App\Rdex\Entity\RdexDriver;
use App\Rdex\Entity\RdexJourney;
use App\Rdex\Entity\RdexOperator;
use App\Rdex\Entity\RdexPassenger;
use App\Rdex\Entity\RdexTripDate;

/**
 * Alternative RDEX result builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexAltJourneyBuilder
{
    private $_result;
    private $_operator;

    public function __construct(array $result, RdexOperator $operator)
    {
        $this->_result = $result;
        $this->_operator = $operator;
    }

    public function build(): array
    {
        $journey = new RdexJourney($this->_result['proposal_id']);
        $journey->setOperator($this->_operator->getName());
        $journey->setOrigin($this->_operator->getOrigin());
        $journey->setUrl('https://'.$this->_operator->getOrigin().'/covoiturage/'.$this->_result['origin'].'/'.$this->_result['destination'].'/'.$this->_result['frequency'].'/1/10');

        $journey->setFrequency(Criteria::FREQUENCY_PUNCTUAL == $this->_result['frequency'] ? RdexJourney::FREQUENCY_PUNCTUAL : RdexJourney::FREQUENCY_REGULAR);

        $journey->setType(RdexJourney::TYPE_ONE_WAY);
        if (!is_null($this->_result['return_times'])) {
            $journey->setType(RdexJourney::TYPE_ROUND_TRIP);
        }

        $isDriver = false;
        $isPassenger = false;

        switch ($this->_result['role']) {
            case Ad::ROLE_DRIVER:
                $isDriver = true;

                break;

            case Ad::ROLE_PASSENGER:
                $isPassenger = true;

                break;

            case Ad::ROLE_DRIVER_OR_PASSENGER:
                $isDriver = true;
                $isPassenger = true;

                break;
        }

        $journey->setDriver($this->_buildDriver($isDriver));
        $journey->setPassenger($this->_buildPassenger($isPassenger));
        $journey->setFrom($this->_buildFrom());
        $journey->setTo($this->_buildTo());

        $journey->setDays($this->_buildDays());

        $journey->setOutward($this->_buildTripDate('outward_times'));
        $journey->setReturn($this->_buildTripDate('return_times'));

        $journey->setDistance($this->_result['distance']);
        $journey->setDuration($this->_result['duration']);
        $journey->setCost(['variable' => $this->_result['price_km']]);

        return ['journeys' => $journey];
    }

    private function _buildFrom(): RdexAddress
    {
        $address = new RdexAddress();
        $address->setCity($this->_result['origin']);
        $address->setLatitude($this->_result['latitude_origin']);
        $address->setLongitude($this->_result['longitude_origin']);

        return $address;
    }

    private function _buildTo(): RdexAddress
    {
        $address = new RdexAddress();
        $address->setCity($this->_result['destination']);
        $address->setLatitude($this->_result['latitude_destination']);
        $address->setLongitude($this->_result['longitude_destination']);

        return $address;
    }

    private function _buildDriver(bool $isDriver): RdexDriver
    {
        $driver = new RdexDriver($this->_result['user_id']);
        $driver->setAlias($this->_result['user_name']);
        if (1 == $this->_result['gender']) {
            $driver->setGender('female');
        } else {
            $driver->setGender('male');
        }

        $driver->setSeats($this->_result['seats_driver']);
        $driver->setState($isDriver ? 1 : 0);

        return $driver;
    }

    private function _buildPassenger(bool $isPassenger): RdexPassenger
    {
        $passenger = new RdexPassenger($this->_result['user_id']);
        $passenger->setAlias($this->_result['user_name']);
        if (1 == $this->_result['gender']) {
            $passenger->setGender('female');
        } else {
            $passenger->setGender('male');
        }
        $passenger->setPersons(0);
        $passenger->setState($isPassenger ? 1 : 0);

        return $passenger;
    }

    private function _buildDays(): RdexDay
    {
        $days = new RdexDay();

        $resultDays = json_decode($this->_result['days'], true);
        foreach ($resultDays as $day => $checked) {
            switch ($day) {
                case 'mon':
                    $days->setMonday((int) $checked);

                    break;

                case 'tue':
                    $days->setTuesday((int) $checked);

                    break;

                case 'wed':
                    $days->setWednesday((int) $checked);

                    break;

                case 'thu':
                    $days->setThursday((int) $checked);

                    break;

                case 'fri':
                    $days->setFriday((int) $checked);

                    break;

                case 'sat':
                    $days->setSaturday((int) $checked);

                    break;

                case 'sun':
                    $days->setSunday((int) $checked);

                    break;
            }
        }

        return $days;
    }

    private function _buildTripDate(string $fieldTimes): ?RdexTripDate
    {
        if (is_null($this->_result[$fieldTimes])) {
            return null;
        }

        $tripDate = new RdexTripDate();
        $tripDate->setMindate($this->_result['from_date']);
        $tripDate->setMaxdate($this->_result['to_date']);

        $resultDays = json_decode($this->_result[$fieldTimes], true);
        foreach ($resultDays as $day => $time) {
            if (is_null($time)) {
                continue;
            }

            $minMaxTime = $this->_computeMinMaxTime($time);
            $rdexDayTime = new RdexDayTime();
            $rdexDayTime->setMintime($minMaxTime[0]->format('H:i:s'));
            $rdexDayTime->setMaxtime($minMaxTime[1]->format('H:i:s'));

            switch ($day) {
                case 'mon':
                    $tripDate->setMonday($rdexDayTime);

                    break;

                case 'tue':
                    $tripDate->setTuesday($rdexDayTime);

                    break;

                case 'wed':
                    $tripDate->setWednesday($rdexDayTime);

                    break;

                case 'thu':
                    $tripDate->setThursday($rdexDayTime);

                    break;

                case 'fri':
                    $tripDate->setFriday($rdexDayTime);

                    break;

                case 'sat':
                    $tripDate->setSaturday($rdexDayTime);

                    break;

                case 'sun':
                    $tripDate->setSunday($rdexDayTime);

                    break;
            }
        }

        return $tripDate;
    }

    /**
     * Compute the min and max time considering the margin time.
     */
    private function _computeMinMaxTime(string $time, int $margin = 900): array
    {
        $mintime = \DateTime::createFromFormat('H:i:s', $time);
        $mintime->sub(new \DateInterval('PT'.$margin.'S'));

        $maxtime = \DateTime::createFromFormat('H:i:s', $time);
        $maxtime->add(new \DateInterval('PT'.$margin.'S'));

        return [$mintime, $maxtime];
    }
}
