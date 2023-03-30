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

namespace App\Carpool\Entity\MobicoopMatcher;

class MarginSchedule implements \JsonSerializable
{
    /**
     * @var int
     */
    private $mon;

    /**
     * @var int
     */
    private $tue;

    /**
     * @var int
     */
    private $wed;

    /**
     * @var int
     */
    private $thu;

    /**
     * @var int
     */
    private $fri;

    /**
     * @var int
     */
    private $sat;

    /**
     * @var int
     */
    private $sun;

    public function setMon(?int $mon)
    {
        $this->mon = $mon;

        return $this;
    }

    public function getMon(): ?int
    {
        return $this->mon;
    }

    public function setTue(?int $tue)
    {
        $this->tue = $tue;

        return $this;
    }

    public function getTue(): ?int
    {
        return $this->tue;
    }

    public function setWed(?int $wed)
    {
        $this->wed = $wed;

        return $this;
    }

    public function getWed(): ?int
    {
        return $this->wed;
    }

    public function setThu(?int $thu)
    {
        $this->thu = $thu;

        return $this;
    }

    public function getThu(): ?int
    {
        return $this->thu;
    }

    public function setFri(?int $fri)
    {
        $this->fri = $fri;

        return $this;
    }

    public function getFri(): ?int
    {
        return $this->fri;
    }

    public function setSat(?int $sat)
    {
        $this->sat = $sat;

        return $this;
    }

    public function getSat(): ?int
    {
        return $this->sat;
    }

    public function setSun(?int $sun)
    {
        $this->sun = $sun;

        return $this;
    }

    public function getSun(): ?int
    {
        return $this->sun;
    }

    public function jsonSerialize()
    {
        return
            [
                'mon' => $this->getMon(),
                'tue' => $this->getTue(),
                'wed' => $this->getWed(),
                'thu' => $this->getThu(),
                'fri' => $this->getFri(),
                'sat' => $this->getSat(),
                'sun' => $this->getSun(),
            ];
    }
}
