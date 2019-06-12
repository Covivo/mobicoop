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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * Carpooling : travel point for a journey.
 */
class Waypoint
{
    /**
     * @var int The id of this point.
     */
    private $id;
    
    /**
     * @var string|null The iri of this proposal.
     */
    private $iri;

    /**
     * @var int Position number of the point in the whole journey.
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $position;

    /**
     * @var boolean The point is the last point of the whole route.
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $destination;

    /**
     * @var Proposal|null The proposal that created the point.
     */
    private $proposal;
    
    /**
     * @var Matching The proposal that created the point.
     */
    private $matching;
    
    /**
     * @var Ask The ask that created the point.
     */
    private $ask;

    /**
     * @var Address The address of the point.
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $address;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(int $position): self
    {
        $this->position = $position;
        
        return $this;
    }
    
    public function isDestination(): ?bool
    {
        return $this->destination;
    }
    
    public function setDestination(bool $isDestination): self
    {
        $this->destination = $isDestination;
        
        return $this;
    }
    
    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }
    
    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;
        
        return $this;
    }
    
    public function getMatching(): ?Matching
    {
        return $this->matching;
    }
    
    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;
        
        return $this;
    }
    
    public function getAsk(): ?Ask
    {
        return $this->ask;
    }
    
    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;
        
        return $this;
    }
    
    public function getAddress(): ?Address
    {
        return $this->address;
    }
    
    public function setAddress(?Address $address): self
    {
        $this->address = $address;
        
        return $this;
    }
}
