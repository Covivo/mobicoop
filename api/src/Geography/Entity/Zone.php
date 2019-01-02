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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Zone entity.
 * This entity delimits a geographic zone by a minimum and maximum longitude and latitude.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 * @ORM\Entity(repositoryClass="App\Geography\Repository\ZoneRepository")
 */
class Zone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $fromLat;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $toLat;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $fromLon;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $toLon;

    /**
     * @ORM\OneToMany(targetEntity="Near::class", mappedBy="zone1")
     */
    private $nears1;

    /**
     * @ORM\OneToMany(targetEntity="Near::class", mappedBy="zone2")
     */
    private $nears2;

    public function __construct($id=null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
        $this->nears1 = new ArrayCollection();
        $this->nears2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromLat(): float
    {
        return $this->fromLat;
    }

    public function setFromLat(float $fromLat): self
    {
        $this->fromLat = $fromLat;

        return $this;
    }

    public function getToLat(): float
    {
        return $this->toLat;
    }

    public function setToLat(float $toLat): self
    {
        $this->toLat = $toLat;

        return $this;
    }

    public function getFromLon(): float
    {
        return $this->fromLon;
    }

    public function setFromLon(float $fromLon): self
    {
        $this->fromLon = $fromLon;

        return $this;
    }

    public function getToLon(): float
    {
        return $this->toLon;
    }

    public function setToLon(float $toLon): self
    {
        $this->toLon = $toLon;

        return $this;
    }

    /**
     * @return Collection|Near[]
     */
    public function getNears1(): Collection
    {
        return $this->nears1;
    }

    public function addNears1(Near $nears1): self
    {
        if (!$this->nears1->contains($nears1)) {
            $this->nears1[] = $nears1;
            $nears1->setZone1($this);
        }

        return $this;
    }

    public function removeNears1(Near $nears1): self
    {
        if ($this->nears1->contains($nears1)) {
            $this->nears1->removeElement($nears1);
            // set the owning side to null (unless already changed)
            if ($nears1->getZone1() === $this) {
                $nears1->setZone1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Near[]
     */
    public function getNears2(): Collection
    {
        return $this->nears2;
    }

    public function addNears2(Near $nears2): self
    {
        if (!$this->nears2->contains($nears2)) {
            $this->nears2[] = $nears2;
            $nears2->setZone2($this);
        }

        return $this;
    }

    public function removeNears2(Near $nears2): self
    {
        if ($this->nears2->contains($nears2)) {
            $this->nears2->removeElement($nears2);
            // set the owning side to null (unless already changed)
            if ($nears2->getZone2() === $this) {
                $nears2->setZone2(null);
            }
        }

        return $this;
    }
}
