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
 * A zone crossed by a direction at a certain precision (useful only for matching calculations).
 *
 * @ORM\Entity
 */
class Zone
{
    /**
     * @var Direction|null The direction.
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", inversedBy="zones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $direction;

    /**
     * @var int The zone.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $zoneid;

    /**
     * @var float|null The precision of the grid in degrees.
     *
     * @ORM\Id()
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $thinness;

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }
    
    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;
        
        return $this;
    }

    public function getZoneid(): ?int
    {
        return $this->zoneid;
    }
    
    public function setZoneid(?int $zoneid): self
    {
        $this->zoneid = $zoneid;
        
        return $this;
    }

    public function getThinness(): ?float
    {
        return $this->thinness;
    }

    public function setThinness(?float $thinness): self
    {
        $this->thinness = $thinness;
        
        return $this;
    }
}
