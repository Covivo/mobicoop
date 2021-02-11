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

namespace App\Carpool\Interoperability\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Regular schedule item for an Interoperability Ad.
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Schedule
{
    
    /**
     * @var boolean|null The journey is available on mondays (if regular).
     * @Groups({"adWrite"})
     */
    private $mon;

    /**
     * @var boolean|null The journey is available on tuesdays (if regular).
     * @Groups({"adWrite"})
     */
    private $tue;

    /**
     * @var boolean|null The journey is available on wednesdays (if regular).
     * @Groups({"adWrite"})
     */
    private $wed;

    /**
     * @var boolean|null The journey is available on thursdays (if regular).
     * @Groups({"adWrite"})
     */
    private $thu;

    /**
     * @var boolean|null The journey is available on fridays (if regular).
     * @Groups({"adWrite"})
     */
    private $fri;

    /**
     * @var boolean|null The journey is available on saturdays (if regular).
     * @Groups({"adWrite"})
     */
    private $sat;

    /**
     * @var boolean|null The journey is available on sundays (if regular).
     * @Groups({"adWrite"})
     */
    private $sun;

    /**
     * @var \DateTimeInterface|null The outward time to display (if regular and unique).
     * @Groups({"adWrite"})
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The return time to display (if regular and unique).
     * @Groups({"adWrite"})
     */
    private $returnTime;

    public function hasMon(): ?bool
    {
        return $this->mon;
    }

    public function setMon(?bool $mon): self
    {
        $this->mon = $mon;

        return $this;
    }

    public function hasTue(): ?bool
    {
        return $this->tue;
    }

    public function setTue(?bool $tue): self
    {
        $this->tue = $tue;

        return $this;
    }

    public function hasWed(): ?bool
    {
        return $this->wed;
    }

    public function setWed(?bool $wed): self
    {
        $this->wed = $wed;

        return $this;
    }

    public function hasThu(): ?bool
    {
        return $this->thu;
    }

    public function setThu(?bool $thu): self
    {
        $this->thu = $thu;

        return $this;
    }

    public function hasFri(): ?bool
    {
        return $this->fri;
    }

    public function setFri(?bool $fri): self
    {
        $this->fri = $fri;

        return $this;
    }

    public function hasSat(): ?bool
    {
        return $this->sat;
    }

    public function setSat(?bool $sat): self
    {
        $this->sat = $sat;

        return $this;
    }

    public function hasSun(): ?bool
    {
        return $this->sun;
    }

    public function setSun(?bool $sun): self
    {
        $this->sun = $sun;

        return $this;
    }

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?\DateTimeInterface $outwardTime): self
    {
        $this->outwardTime = $outwardTime;

        return $this;
    }

    public function getReturnTime(): ?\DateTimeInterface
    {
        return $this->returnTime;
    }

    public function setReturnTime(?\DateTimeInterface $returnTime): self
    {
        $this->returnTime = $returnTime;

        return $this;
    }
}
