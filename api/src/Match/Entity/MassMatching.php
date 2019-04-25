<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Match\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Geography\Entity\Direction;

/**
 * A potential matching between 2 persons from a mass file import.
 * 
 * @ORM\Entity
 */
class MassMatching
{
    /**
     * @var int The id of this matching.
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var MassPerson The first person.
     * @ORM\OneToOne(targetEntity="\App\Match\Entity\MassPerson", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $massPerson1;

    /**
     * @var MassPerson The second person.
     * @ORM\OneToOne(targetEntity="\App\Match\Entity\MassPerson", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $massPerson2;

    /**
     * @var Direction|null The direction for the 2 persons to their final destination.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"})
     */
    private $direction;

    /**
     * @var int|null The saved CO2 thanks to this matching.
     * @ORM\Column(type="integer")
     */
    private $co2;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMassPerson1(): MassPerson
    {
        return $this->massPerson1;
    }

    public function setMassPerson1(MassPerson $massPerson1): self
    {
        $this->massPerson1 = $massPerson1;

        return $this;
    }

    public function getMassPerson2(): MassPerson
    {
        return $this->massPerson2;
    }

    public function setMassPerson2(MassPerson $massPerson2): self
    {
        $this->massPerson2 = $massPerson2;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getCo2(): int
    {
        return $this->co2;
    }

    public function setCo2(int $co2): self
    {
        $this->co2 = $co2;

        return $this;
    }
}
