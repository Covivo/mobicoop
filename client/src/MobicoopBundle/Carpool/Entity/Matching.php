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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Carpooling : matching between an offer and a request.
 */
class Matching implements \JsonSerializable
{
    /**
     * @var int The id of this matching.
     */
    private $id;
    
    /**
     * @var string|null The iri of this matching.
     */
    private $iri;

    /**
     * @var Proposal The offer proposal.
     *
     * @Assert\NotBlank
     */
    private $proposalOffer;

    /**
     * @var Proposal The request proposal.
     *
     * @Assert\NotBlank
     */
    private $proposalRequest;

    /**
     * @var Criteria The criteria applied to this matching.
     *
     * @Assert\NotBlank
     */
    private $criteria;

    /**
     * @var Waypoint[] The waypoints of the matching.
     *
     * @Assert\NotBlank
     */
    private $waypoints;

    /**
     * @var array|null The resulting filters of the matching.
     */
    private $filters;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/matchings/".$id);
        }
        $this->waypoints = new ArrayCollection();
    }

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

    public function getProposalOffer(): ?Proposal
    {
        return $this->proposalOffer;
    }
    
    public function setProposalOffer(?Proposal $proposalOffer): self
    {
        $this->proposalOffer = $proposalOffer;
        
        return $this;
    }
    
    public function getProposalRequest(): ?Proposal
    {
        return $this->proposalRequest;
    }
    
    public function setProposalRequest(?Proposal $proposalRequest): self
    {
        $this->proposalRequest = $proposalRequest;
        
        return $this;
    }
    
    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }
    
    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;
        
        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }
    
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        
        return $this;
    }

    /**
     * @return Collection|Waypoint[]
     */
    public function getWaypoints()
    {
        return $this->waypoints;
    }
    
    public function addWaypoint(Waypoint $waypoint): self
    {
        if (!$this->waypoints->contains($waypoint)) {
            $this->waypoints[] = $waypoint;
            $waypoint->setMatching($this);
        }
        
        return $this;
    }
    
    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getMatching() === $this) {
                $waypoint->setMatching(null);
            }
        }
        
        return $this;
    }

    // If you want more info from matching you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'proposalOffer'     => $this->getProposalOffer(),
            'proposalRequest'   => $this->getProposalRequest(),
            'criteria'          => $this->getCriteria(),
            'waypoint'          => $this->getWaypoints()
        ];
    }
}
