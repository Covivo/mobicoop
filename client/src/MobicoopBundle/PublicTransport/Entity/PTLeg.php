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

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;

/**
 * A leg of a journey.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLeg
{
    /**
     * @var int The id of this leg.
     */
    private $id;
    
    /**
     * @var string The indication of this leg.
     */
    private $indication;
    
    /**
     * @var int The distance of this leg.
     */
    private $distance;
    
    /**
     * @var string The duration of this leg.
     */
    private $duration;
    
    /**
     * @var int The position of this leg.
     */
    private $position;
    
    /**
     * @var bool The leg is the last leg of the journey.
     */
    private $isLast;
    
    /**
     * @var string The magnetic direction of this leg.
     */
    private $magneticDirection;
    
    /**
     * @var string The relative direction of this leg.
     */
    private $relativeDirection;
    
    /**
     * @var PTJourney The parent journey of this leg.
     */
    private $ptjourney;
    
    /**
     * @var PTDeparture The departure of this leg.
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this leg.
     */
    private $ptarrival;
    
    /**
     * @var TravelMode The transport mode of this leg.
     */
    private $travelMode;
    
    /**
     * @var PTLine The public transport line of this leg.
     */
    private $ptline;
    
    /**
     * @var string The direction of the public transport line of this leg.
     */
    private $direction;
    
    /**
     * @var PTStep[] The steps of this leg.
     */
    private $ptsteps;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->setPosition($id);
        $this->ptsteps = new ArrayCollection();
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getIndication(): ?string
    {
        return $this->indication;
    }
    
    public function setIndication(?string $indication): self
    {
        $this->indication = $indication;
        
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
    
    public function getDuration(): ?string
    {
        return $this->duration;
    }
    
    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;
        
        return $this;
    }
    
    public function getPosition(): int
    {
        return $this->position;
    }
    
    public function setPosition(int $position): self
    {
        $this->position = $position;
        
        return $this;
    }
    
    public function isLast(): bool
    {
        return $this->isLast;
    }
    
    public function setIsLast(bool $isLast): self
    {
        $this->isLast = $isLast;
        
        return $this;
    }
    
    public function getMagneticDirection(): ?string
    {
        return $this->magneticDirection;
    }
    
    public function setMagneticDirection(?string $magneticDirection): self
    {
        $this->magneticDirection = $magneticDirection;
        
        return $this;
    }
    
    public function getRelativeDirection(): ?string
    {
        return $this->relativeDirection;
    }
    
    public function setRelativeDirection(?string $relativeDirection): self
    {
        $this->relativeDirection = $relativeDirection;
        
        return $this;
    }
    
    public function getPTJourney(): PTJourney
    {
        return $this->ptjourney;
    }
    
    public function setPTJourney(PTJourney $ptjourney): self
    {
        $this->ptjourney = $ptjourney;
        
        return $this;
    }
    
    public function getPTDeparture(): ?PTDeparture
    {
        return $this->ptdeparture;
    }
    
    public function setPTDeparture(?PTDeparture $ptdeparture): self
    {
        $this->ptdeparture = $ptdeparture;
        
        return $this;
    }
    
    public function getPTArrival(): ?PTArrival
    {
        return $this->ptarrival;
    }
    
    public function setPTArrival(?PTArrival $ptarrival): self
    {
        $this->ptarrival = $ptarrival;
        
        return $this;
    }
    
    public function getTravelMode(): TravelMode
    {
        return $this->travelMode;
    }
    
    public function setTravelMode(TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;
        
        return $this;
    }
    
    public function getPTLine(): ?PTLine
    {
        return $this->ptline;
    }
    
    public function setPTLine(?PTLine $ptline): self
    {
        $this->ptline = $ptline;
        
        return $this;
    }
    
    public function getDirection(): ?string
    {
        return $this->direction;
    }
    
    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;
        
        return $this;
    }
    
    public function getPTSteps(): Collection
    {
        return $this->ptsteps;
    }
    
    public function setPTSteps(ArrayCollection $ptsteps): self
    {
        $this->ptsteps = $ptsteps;
        
        return $this;
    }
    
    public function addPTStep(PTStep $ptstep): self
    {
        if (!$this->ptsteps->contains($ptstep)) {
            $this->ptsteps->add($ptstep);
            $ptstep->setPTLeg($this);
        }
        
        return $this;
    }
    
    public function removePTLeg(PTStep $ptstep): self
    {
        if ($this->ptsteps->contains($ptstep)) {
            $this->ptsteps->removeElement($ptstep);
            // set the owning side to null (unless already changed)
            if ($ptstep->getPTLeg() === $this) {
                $ptstep->setPTLeg(null);
            }
        }
        
        return $this;
    }
}
