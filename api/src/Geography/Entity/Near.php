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
use App\Geography\Entity\Zone;

/**
 * Near entity.
 * This entity gives a list of nearby zones.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 * @ORM\Entity
 */
class Near
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Geography\Entity\Zone", inversedBy="nears1")
     * @ORM\JoinColumn(nullable=false)
     */
    private $zone1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Geography\Entity\Zone", inversedBy="nears2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $zone2;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZone1(): ?Zone
    {
        return $this->zone1;
    }

    public function setZone1(?Zone $zone1): self
    {
        $this->zone1 = $zone1;

        return $this;
    }

    public function getZone2(): ?Zone
    {
        return $this->zone2;
    }

    public function setZone2(?Zone $zone2): self
    {
        $this->zone2 = $zone2;

        return $this;
    }
}
