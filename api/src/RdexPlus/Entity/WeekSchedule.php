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

/**
 * RDEX+ : A WeekSchedule (for regular Journeys)
 * Documentation : https://rdex.fabmob.io/
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WeekSchedule
{
    /*
    * WARNING : For now, Mobicoop only use the outward timeDelta and ignore the time delta of specific days
    */

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Mondays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $mondayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $mondayTimeDelta;

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Tuesdays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $tuesdayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $tuesdayTimeDelta;

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Wednesdays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $wednesdayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $wednesdayTimeDelta;

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Thursdays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $thursdayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $thursdayTimeDelta;

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Fridays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $fridayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $fridayTimeDelta;

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Saturdays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $saturdayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $saturdayTimeDelta;

    /**
     * @var string Time using a UTC partial time string (example "08:30:00") of departure for the journey (outward or return) on Sundays, if any.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $sundayTime;

    /**
     * @var int Optional time margin in seconds.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $sundayTimeDelta;


    public function getMondayTime(): ?string
    {
        return $this->mondayTime;
    }

    public function setMondayTime(?string $mondayTime): self
    {
        $this->mondayTime = $mondayTime;

        return $this;
    }

    public function getMondayTimeDelta(): ?int
    {
        return $this->mondayTimeDelta;
    }

    public function setMondayTimeDelta(?int $mondayTimeDelta): self
    {
        $this->mondayTimeDelta = $mondayTimeDelta;

        return $this;
    }

    public function getTuesdayTime(): ?string
    {
        return $this->tuesdayTime;
    }

    public function setTuesdayTime(?string $tuesdayTime): self
    {
        $this->tuesdayTime = $tuesdayTime;

        return $this;
    }

    public function getTuesdayTimeDelta(): ?int
    {
        return $this->tuesdayTimeDelta;
    }

    public function setTuesdayTimeDelta(?int $tuesdayTimeDelta): self
    {
        $this->tuesdayTimeDelta = $tuesdayTimeDelta;

        return $this;
    }

    public function getWednesdayTime(): ?string
    {
        return $this->wednesdayTime;
    }

    public function setWednesdayTime(?string $wednesdayTime): self
    {
        $this->wednesdayTime = $wednesdayTime;

        return $this;
    }

    public function getWednesdayTimeDelta(): ?int
    {
        return $this->wednesdayTimeDelta;
    }

    public function setWednesdayTimeDelta(?int $wednesdayTimeDelta): self
    {
        $this->wednesdayTimeDelta = $wednesdayTimeDelta;

        return $this;
    }

    public function getThursdayTime(): ?string
    {
        return $this->thursdayTime;
    }

    public function setThursdayTime(?string $thursdayTime): self
    {
        $this->thursdayTime = $thursdayTime;

        return $this;
    }

    public function getThursdayTimeDelta(): ?int
    {
        return $this->thursdayTimeDelta;
    }

    public function setThursdayTimeDelta(?int $thursdayTimeDelta): self
    {
        $this->thursdayTimeDelta = $thursdayTimeDelta;

        return $this;
    }

    public function getFridayTime(): ?string
    {
        return $this->fridayTime;
    }

    public function setFridayTime(?string $fridayTime): self
    {
        $this->fridayTime = $fridayTime;

        return $this;
    }

    public function getFridayTimeDelta(): ?int
    {
        return $this->fridayTimeDelta;
    }

    public function setFridayTimeDelta(?int $fridayTimeDelta): self
    {
        $this->fridayTimeDelta = $fridayTimeDelta;

        return $this;
    }

    public function getSaturdayTime(): ?string
    {
        return $this->saturdayTime;
    }

    public function setSaturdayTime(?string $saturdayTime): self
    {
        $this->saturdayTime = $saturdayTime;

        return $this;
    }

    public function getSaturdayTimeDelta(): ?int
    {
        return $this->saturdayTimeDelta;
    }

    public function setSaturdayTimeDelta(?int $saturdayTimeDelta): self
    {
        $this->saturdayTimeDelta = $saturdayTimeDelta;

        return $this;
    }

    public function getSundayTime(): ?string
    {
        return $this->sundayTime;
    }

    public function setSundayTime(?string $sundayTime): self
    {
        $this->sundayTime = $sundayTime;

        return $this;
    }

    public function getSundayTimeDelta(): ?int
    {
        return $this->sundayTimeDelta;
    }

    public function setSundayTimeDelta(?int $sundayTimeDelta): self
    {
        $this->sundayTimeDelta = $sundayTimeDelta;

        return $this;
    }
}
