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

class Schedule implements \JsonSerializable
{
    /**
     * @var string
     */
    private $mon;

    /**
     * @var string
     */
    private $tue;

    /**
     * @var string
     */
    private $wed;

    /**
     * @var string
     */
    private $thu;

    /**
     * @var string
     */
    private $fri;

    /**
     * @var string
     */
    private $sat;

    /**
     * @var string
     */
    private $sun;

    public function setMon(?string $mon)
    {
        $this->mon = $mon;

        return $this;
    }

    public function getMon(): ?string
    {
        return $this->mon;
    }

    public function setTue(?string $tue)
    {
        $this->tue = $tue;

        return $this;
    }

    public function getTue(): ?string
    {
        return $this->tue;
    }

    public function setWed(?string $wed)
    {
        $this->wed = $wed;

        return $this;
    }

    public function getWed(): ?string
    {
        return $this->wed;
    }

    public function setThu(?string $thu)
    {
        $this->thu = $thu;

        return $this;
    }

    public function getThu(): ?string
    {
        return $this->thu;
    }

    public function setFri(?string $fri)
    {
        $this->fri = $fri;

        return $this;
    }

    public function getFri(): ?string
    {
        return $this->fri;
    }

    public function setSat(?string $sat)
    {
        $this->sat = $sat;

        return $this;
    }

    public function getSat(): ?string
    {
        return $this->sat;
    }

    public function setSun(?string $sun)
    {
        $this->sun = $sun;

        return $this;
    }

    public function getSun(): ?string
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
