<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\RdexPlus\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\RdexPlus\Entity\WeekSchedule;

/**
 * RDEX+ : A WaySchedule
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WaySchedule
{

    /**
     * @var int if frequency=punctual orboth, departureDate specifies departure datetime using a UNIX UTC timestamp in seconds.
     * If not specified, the timestamp of the request is considered the expected departure datetime.
     * If frequency=regular, departureDate specifies the beginning of the validity period for the regular journey
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $departureDate;

    /**
     * @var int If frequency=regular or both, maxDate specifies the end of the validity period for the regular journey, as a datetime using a UNIX UTC timestamp in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $maxDate;

    /**
     * @var WeekSchedule[] If frequency=regular, this parameter specifies the schedule of expected regular journey.
     * If several WeekSchedule objects are passed in the array, the journey is expected to happened on all given time slots (two departures the same day is considered a possible case).
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $regularSchedule;

    /**
     * @var int Time margin in seconds
     * If frequency=regular, this timeDelta is taken into account only if no other value is specified for the specific day
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $timeDelta;


    public function __construct()
    {
    }

    public function getDepartureDate(): ?int
    {
        return $this->departureDate;
    }

    public function setDepartureDate(?int $departureDate): self
    {
        $this->departureDate = $departureDate;

        return $this;
    }
    
    public function getMaxDate(): ?int
    {
        return $this->maxDate;
    }

    public function setMaxDate(?int $maxDate): self
    {
        $this->maxDate = $maxDate;

        return $this;
    }
   
    public function isRegularSchedule(): ?array
    {
        return $this->regularSchedule;
    }

    public function setRegularSchedule(?array $regularSchedule): self
    {
        $this->regularSchedule = $regularSchedule;

        return $this;
    }
    
    public function getTimeDelta(): ?int
    {
        return $this->timeDelta;
    }

    public function setTimeDelta(?int $timeDelta): self
    {
        $this->timeDelta = $timeDelta;

        return $this;
    }
}
