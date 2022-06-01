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
 **************************/

namespace App\Rdex\Entity;

/**
 * An RDEX Trip Date.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexTripDate implements \JsonSerializable
{
    /**
     * @var \DateTime The min date.
     */
    private $mindate;

    /**
     * @var \DateTime The max date.
     */
    private $maxdate;

    /**
     * @var RdexDayTime The day time for monday.
     */
    private $monday;

    /**
     * @var RdexDayTime The day time for tuesday.
     */
    private $tuesday;

    /**
     * @var RdexDayTime The day time for wednesday.
     */
    private $wednesday;

    /**
     * @var RdexDayTime The day time for thursday.
     */
    private $thursday;

    /**
     * @var RdexDayTime The day time for friday.
     */
    private $friday;

    /**
     * @var RdexDayTime The day time for saturday.
     */
    private $saturday;

    /**
     * @var RdexDayTime The day time for sunday.
     */
    private $sunday;

    /**
     * @return \DateTime
     */
    public function getMindate(): \DateTime
    {
        return $this->mindate;
    }

    /**
     * @return \DateTime
     */
    public function getMaxdate(): \DateTime
    {
        return $this->maxdate;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getMonday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->monday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getTuesday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->tuesday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getWednesday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->wednesday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getThursday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->thursday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getFriday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->friday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getSaturday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->saturday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getSunday(): \App\Rdex\Entity\RdexDayTime
    {
        return $this->sunday;
    }

    /**
     * @param \DateTime $mindate
     */
    public function setMindate($mindate)
    {
        $this->mindate = $mindate;
    }

    /**
     * @param \DateTime $maxdate
     */
    public function setMaxdate($maxdate)
    {
        $this->maxdate = $maxdate;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $monday
     */
    public function setMonday($monday)
    {
        $this->monday = $monday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $tuesday
     */
    public function setTuesday($tuesday)
    {
        $this->tuesday = $tuesday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $wednesday
     */
    public function setWednesday($wednesday)
    {
        $this->wednesday = $wednesday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $thursday
     */
    public function setThursday($thursday)
    {
        $this->thursday = $thursday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $friday
     */
    public function setFriday($friday)
    {
        $this->friday = $friday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $saturday
     */
    public function setSaturday($saturday)
    {
        $this->saturday = $saturday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $sunday
     */
    public function setSunday($sunday)
    {
        $this->sunday = $sunday;
    }

    public function jsonSerialize(): mixed
    {
        return
        [
            'mindate'   => $this->getMindate(),
            'maxdate'   => $this->getMaxdate(),
            'monday'    => $this->getMonday(),
            'tuesday'   => $this->getTuesday(),
            'wednesday' => $this->getWednesday(),
            'thursday'  => $this->getThursday(),
            'friday'    => $this->getFriday(),
            'saturday'  => $this->getSaturday(),
            'sunday'    => $this->getSunday()
        ];
    }
}
