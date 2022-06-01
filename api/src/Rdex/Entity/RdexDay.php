<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Rdex\Entity;

/**
 * An RDEX Day.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexDay implements \JsonSerializable
{
    /**
     * @var int the state for monday
     */
    private $monday;

    /**
     * @var int the state for tuesday
     */
    private $tuesday;

    /**
     * @var int the state for wednesday
     */
    private $wednesday;

    /**
     * @var int the state for thursday
     */
    private $thursday;

    /**
     * @var int the state for friday
     */
    private $friday;

    /**
     * @var int the state for saturday
     */
    private $saturday;

    /**
     * @var int the state for sunday
     */
    private $sunday;

    public function __construct()
    {
        $this->monday = 0;
        $this->tuesday = 0;
        $this->wednesday = 0;
        $this->thursday = 0;
        $this->friday = 0;
        $this->saturday = 0;
        $this->sunday = 0;
    }

    public function getMonday(): number
    {
        return $this->monday;
    }

    public function getTuesday(): number
    {
        return $this->tuesday;
    }

    public function getWednesday(): number
    {
        return $this->wednesday;
    }

    public function getThursday(): number
    {
        return $this->thursday;
    }

    public function getFriday(): number
    {
        return $this->friday;
    }

    public function getSaturday(): number
    {
        return $this->saturday;
    }

    public function getSunday(): number
    {
        return $this->sunday;
    }

    /**
     * @param number $monday
     */
    public function setMonday($monday)
    {
        $this->monday = $monday;
    }

    /**
     * @param number $tuesday
     */
    public function setTuesday($tuesday)
    {
        $this->tuesday = $tuesday;
    }

    /**
     * @param number $wednesday
     */
    public function setWednesday($wednesday)
    {
        $this->wednesday = $wednesday;
    }

    /**
     * @param number $thursday
     */
    public function setThursday($thursday)
    {
        $this->thursday = $thursday;
    }

    /**
     * @param number $friday
     */
    public function setFriday($friday)
    {
        $this->friday = $friday;
    }

    /**
     * @param number $saturday
     */
    public function setSaturday($saturday)
    {
        $this->saturday = $saturday;
    }

    /**
     * @param number $sunday
     */
    public function setSunday($sunday)
    {
        $this->sunday = $sunday;
    }

    public function jsonSerialize(): mixed
    {
        return
        [
            'monday' => $this->getMonday(),
            'tuesday' => $this->getTuesday(),
            'wednesday' => $this->getWednesday(),
            'thursday' => $this->getThursday(),
            'friday' => $this->getFriday(),
            'saturday' => $this->getSaturday(),
            'sunday' => $this->getSunday(),
        ];
    }
}
