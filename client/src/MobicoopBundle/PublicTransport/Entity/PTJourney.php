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
 */

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * A public transport journey.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTJourney
{
    /**
     * @var int the id of this journey
     */
    private $id;

    /**
     * @var int the total distance of this journey
     */
    private $distance;

    /**
     * @var int the total duration of this journey
     */
    private $duration;

    /**
     * @var int the number of changes of this journey
     */
    private $changeNumber;

    /**
     * @var float the estimated price of this journey
     */
    private $price;

    /**
     * @var int the estimated CO2 emission of this journey
     */
    private $co2;

    /**
     * @var PTDeparture the departure of this journey
     */
    private $ptdeparture;

    /**
     * @var PTArrival the arrival of this journey
     */
    private $ptarrival;

    /**
     * @var PTLeg[] the legs of this journey
     */
    private $ptlegs;

    /**
     * @var string PT provider name to display on results
     */
    private $ptProviderName;

    /**
     * @var string PT provider url where to find the result
     */
    private $ptProviderUrl;

    public function __construct()
    {
        $this->ptlegs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getChangeNumber(): ?int
    {
        return $this->changeNumber;
    }

    public function setChangeNumber(?int $changeNumber): self
    {
        $this->changeNumber = $changeNumber;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCo2(): ?int
    {
        return $this->co2;
    }

    public function setCo2(?int $co2): self
    {
        $this->co2 = $co2;

        return $this;
    }

    public function getPTDeparture(): PTDeparture
    {
        return $this->ptdeparture;
    }

    public function setPTDeparture(PTDeparture $ptdeparture): self
    {
        $this->ptdeparture = $ptdeparture;

        return $this;
    }

    public function getPTArrival(): PTArrival
    {
        return $this->ptarrival;
    }

    public function setPTArrival(PTArrival $ptarrival): self
    {
        $this->ptarrival = $ptarrival;

        return $this;
    }

    public function getPTLegs(): Collection
    {
        return $this->ptlegs;
    }

    public function setPTLegs(ArrayCollection $ptlegs): self
    {
        $this->ptlegs = $ptlegs;

        return $this;
    }

    public function addPTLeg(PTLeg $ptleg): self
    {
        if (!$this->ptlegs->contains($ptleg)) {
            $this->ptlegs->add($ptleg);
            $ptleg->setPTJourney($this);
        }

        return $this;
    }

    public function removePTLeg(PTLeg $ptleg): self
    {
        if ($this->ptlegs->contains($ptleg)) {
            $this->ptlegs->removeElement($ptleg);
            // set the owning side to null (unless already changed)
            if ($ptleg->getPTJourney() === $this) {
                $ptleg->setPTJourney(null);
            }
        }

        return $this;
    }

    public function getPtProviderName(): ?string
    {
        return $this->ptProviderName;
    }

    public function setPtProviderName(?string $ptProviderName): self
    {
        $this->ptProviderName = $ptProviderName;

        return $this;
    }

    public function getPtProviderUrl(): ?string
    {
        return $this->ptProviderUrl;
    }

    public function setPtProviderUrl(?string $ptProviderUrl): self
    {
        $this->ptProviderUrl = $ptProviderUrl;

        return $this;
    }
}
