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

namespace App\Geography\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Geography\Entity\Direction;

/**
 * A zone crossed by a direction at a certain precision.
 *
 * @ORM\Entity
 */
class Cross
{
    /**
     * @var Direction|null The direction.
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", inversedBy="crosses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $direction;

    /**
     * @var int The zone.
     *
     * @ORM\Id()
     */
    private $zone;

    /**
     * @var float|null The precision of the grid in degrees.
     *
     * @ORM\Id()
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $precision;

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }
    
    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;
        
        return $this;
    }

    public function getZone(): ?int
    {
        return $this->zone;
    }
    
    public function setZone(?int $zone): self
    {
        $this->zone = $zone;
        
        return $this;
    }

    public function getPrecision(): ?float
    {
        return $this->precision;
    }

    public function setPrecision(?float $precision): self
    {
        $this->precision = $precision;
        
        return $this;
    }
    
}