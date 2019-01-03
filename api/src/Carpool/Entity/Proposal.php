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

namespace App\Carpool\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Carpool\Controller\ProposalPost;
use App\Travel\Entity\TravelMode;
use App\User\Entity\User;
use App\Carpool\Filter\LocalityFilter;

/**
 * Carpooling : proposal (offer from a driver / request from a passenger).
 *
 * @ORM\Entity(repositoryClass="App\Carpool\Repository\ProposalRepository")
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "path"="/proposals",
 *              "controller"=ProposalPost::class,
 *          }
 *      },
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(LocalityFilter::class, properties={"startLocality","destinationLocality"})
 * @ApiFilter(NumericFilter::class, properties={"proposalType"})
 * @ApiFilter(DateFilter::class, properties={"criteria.fromDate"})
 */
class Proposal
{
    const TYPE_ONE_WAY = 1;
    const TYPE_OUTWARD = 2;
    const TYPE_RETURN = 3;
    
    /**
     * @var int The id of this proposal.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int The proposal type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $type;

    /**
     * @var \DateTimeInterface Creation date of the proposal.
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var Proposal|null Linked proposal for a round trip (return or outward journey).
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Proposal", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalLinked;
    
    /**
     * @var User|null User who submits the proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="proposals")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Waypoint[] The waypoints of the proposal.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $waypoints;
    
    /**
     * @var TravelMode[]|null The travel modes accepted if the proposal is a request.
     *
     * @ORM\ManyToMany(targetEntity="\App\Travel\Entity\TravelMode")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $travelModes;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is an offer).
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Matching", mappedBy="proposalOffer")
     * @ApiSubresource(maxDepth=1)
     */
    private $matchingRequests;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is a request).
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Matching", mappedBy="proposalRequest")
     * @ApiSubresource(maxDepth=1)
     */
    private $matchingOffers;

    /**
     * @var Criteria The criteria applied to the proposal.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $criteria;
    
    /**
     * @var IndividualStop[] The individual stops of the proposal.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\IndividualStop", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $individualStops;
    
    private $originLocality;
    private $destinationLocality;
    
    public function __construct()
    {
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
    }
    
    public function __clone()
    {
        // when we clone a Proposal we keep only the basic properties, we re-initialize all the collections
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
    
    public function getProposalLinked(): ?self
    {
        return $this->proposalLinked;
    }
    
    public function setProposalLinked(?self $proposalLinked): self
    {
        $this->proposalLinked = $proposalLinked;
        
        // set (or unset) the owning side of the relation if necessary
        $newProposalLinked = $proposalLinked === null ? null : $this;
        if ($newProposalLinked !== $proposalLinked->getProposalLinked()) {
            $proposalLinked->setProposalLinked($newProposalLinked);
        }
        
        return $this;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Waypoint[]
     */
    public function getWaypoints(): Collection
    {
        return $this->waypoints;
    }

    public function addWaypoint(Waypoint $waypoint): self
    {
        if (!$this->waypoints->contains($waypoint)) {
            $this->waypoints[] = $waypoint;
            $waypoint->setProposal($this);
        }

        return $this;
    }

    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getProposal() === $this) {
                $waypoint->setProposal(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection|TravelMode[]
     */
    public function getTravelModes(): Collection
    {
        return $this->travelModes;
    }
    
    public function addTravelMode(TravelMode $travelMode): self
    {
        if (!$this->travelModes->contains($travelMode)) {
            $this->travelModes[] = $travelMode;
        }
        
        return $this;
    }
    
    public function removeTravelMode(TravelMode $travelMode): self
    {
        if ($this->travelModes->contains($travelMode)) {
            $this->travelModes->removeElement($travelMode);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Matching[]
     */
    public function getMatchingRequests(): Collection
    {
        return $this->matchingRequests;
    }

    public function addMatchingRequest(Matching $matchingRequest): self
    {
        if (!$this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests[] = $matchingRequest;
            $matchingRequest->setProposalOffer($this);
        }

        return $this;
    }

    public function removeMatchingRequest(Matching $matchingRequest): self
    {
        if ($this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests->removeElement($matchingRequest);
            // set the owning side to null (unless already changed)
            if ($matchingRequest->getProposalOffer() === $this) {
                $matchingRequest->setProposalOffer(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection|Matching[]
     */
    public function getMatchingOffers(): Collection
    {
        return $this->matchingOffers;
    }
    
    public function addMatchingOffer(Matching $matchingOffer): self
    {
        if (!$this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers[] = $matchingOffer;
            $matchingOffer->setProposalRequest($this);
        }
        
        return $this;
    }
    
    public function removeMatchingOffer(Matching $matchingOffer): self
    {
        if ($this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers->removeElement($matchingOffer);
            // set the owning side to null (unless already changed)
            if ($matchingOffer->getProposalRequest() === $this) {
                $matchingOffer->setProposalRequest(null);
            }
        }
        
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
    
    /**
     * @return Collection|IndividualStop[]
     */
    public function getIndividualStops(): Collection
    {
        return $this->individualStops;
    }
    
    public function addIndividualStop(IndividualStop $individualStop): self
    {
        if (!$this->individualStops->contains($individualStop)) {
            $this->individualStops[] = $individualStop;
            $individualStop->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeIndividualStop(IndividualStop $individualStop): self
    {
        if ($this->individualStops->contains($individualStop)) {
            $this->individualStops->removeElement($individualStop);
            // set the owning side to null (unless already changed)
            if ($individualStop->getProposal() === $this) {
                $individualStop->setProposal(null);
            }
        }
        
        return $this;
    }
    
    public function getOriginLocality()
    {
        return $this->originLocality;
    }

    public function setOriginLocality($originLocality)
    {
        $this->originLocality = $originLocality;
    }
    
    public function getDestinationLocality()
    {
        return $this->destinationLocality;
    }

    public function setDestinationLocality($destinationLocality)
    {
        $this->destinationLocality = $destinationLocality;
    }

    // DOCTRINE EVENTS
    
    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }
}
