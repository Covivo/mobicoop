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

use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * Carpooling : an individual stop.
 * Individual stop is a virtual public transport stop made from an offer proposal.
 * It is used for multimodal calculation. It is calculated only for offer proposal, in regions that are covered by public transportation.
 */
class IndividualStop
{
    /**
     * @var int The id of this stop.
     */
    private $id;

    /**
     * @var int Position number of the stop in the whole route (all the individual stops of the route).
     *
     * @Assert\NotBlank
     */
    private $position;
    
    /**
     * @var int Estimated stop delay in seconds (calculated with 0 as origin).
     */
    private $delay;

    /**
     * @var Proposal The proposal that owns the stop.
     *
     * @Assert\NotBlank
     */
    private $proposal;
    
    /**
     * @var Address The address of the stop.
     *
     * @Assert\NotBlank
     */
    private $address;
    
    public function getId(): ?int
    {
        return $this->id;
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
    
    public function getDelay(): ?int
    {
        return $this->delay;
    }
    
    public function setDelay(int $delay): self
    {
        $this->delay = $delay;
        
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
