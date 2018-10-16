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
Class Proposal 
{
    CONST PROPOSAL_TYPE_OFFER = 1;
    CONST PROPOSAL_TYPE_REQUEST = 2;
    CONST PROPOSAL_TYPE_BOTH = 3;
    CONST JOURNEY_TYPE_ONE_WAY = 1;
    CONST JOURNEY_TYPE_OUTWARD = 2;
    CONST JOURNEY_TYPE_RETURN = 3;
    
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
     * @var int The proposal type (1 = offer (as a driver); 2 = request (as a passenger)).
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $proposalType;

    /**
     * @var int The journey type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $journeyType;

    /**
     * @var \DateTimeInterface Creation date of the proposal.
     * 
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var int|null Real distance of the full journey in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceReal;

    /**
     * @var int|null Flying distance of the full journey in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceFly;

    /**
     * @var int|null Estimated duration of the full journey in seconds (based on real distance).
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $duration;

    /**
     * @var string|null Main cape of the journey (N/S/E/W)
     * 
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Groups({"read"})
     */
    private $cape;

    /**
     * @var Proposal|null Linked proposal for an offer AND request proposal (= request linked for an offer proposal, offer linked for a request proposal).
     * 
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Proposal", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read"})
     * @MaxDepth(1)
     * 
     */
    private $proposalLinked;
    
    /**
     * @var Proposal|null Linked proposal for a round trip (return or outward journey).
     *
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Proposal", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalLinkedJourney;
    
    /**
     * @var Proposal|null Original proposal if calculated proposal.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Proposal")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalOrigin;
    
    /**
     * @var User|null User who submits the proposal.
     * 
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="proposals")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Point[] The points of the proposal.
     * 
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Point", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $points;
    
    /**
     * @var TravelMode[]|null The travel modes accepted if the proposal is a request.
     * 
     * @ORM\ManyToMany(targetEntity="App\Carpool\Entity\TravelMode")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $travelModes;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is an offer).
     * 
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Matching", mappedBy="proposalOffer")
     * @ApiSubresource(maxDepth=1)
     */
    private $matchingRequests;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is a request).
     * 
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Matching", mappedBy="proposalRequest")
     * @ApiSubresource(maxDepth=1)
     */
    private $matchingOffers;

    /**
     * @var Criteria The criteria applied to the proposal.
     * 
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $criteria;
    
    private $startLocality;
    private $destinationLocality;
    
    public function __construct()
    {
        $this->points = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
    }
    
    public function __clone() {
        // when we clone a Proposal we keep only the basic properties, we re-initialize all the collections
        $this->points = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getProposalType(): ?int
    {
        return $this->proposalType;
    }

    public function setProposalType(int $proposalType): self
    {
        $this->proposalType = $proposalType;

        return $this;
    }

    public function getJourneyType(): ?int
    {
        return $this->journeyType;
    }

    public function setJourneyType(int $journeyType): self
    {
        $this->journeyType = $journeyType;

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

    public function getDistanceReal(): ?int
    {
        return $this->distanceReal;
    }

    public function setDistanceReal(?int $distanceReal): self
    {
        $this->distanceReal = $distanceReal;

        return $this;
    }

    public function getDistanceFly(): ?int
    {
        return $this->distanceFly;
    }

    public function setDistanceFly(?int $distanceFly): self
    {
        $this->distanceFly = $distanceFly;

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

    public function getCape(): ?string
    {
        return $this->cape;
    }
    
    public function setCape(string $cape): self
    {
        $this->cape = $cape;
        
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
    
    public function getProposalLinkedJourney(): ?self
    {
        return $this->proposalLinkedJourney;
    }
    
    public function setProposalLinkedJourney(?self $proposalLinkedJourney): self
    {
        $this->proposalLinkedJourney = $proposalLinkedJourney;
        
        // set (or unset) the owning side of the relation if necessary
        $newProposalLinkedJourney = $proposalLinkedJourney === null ? null : $this;
        if ($newProposalLinkedJourney !== $proposalLinkedJourney->getProposalLinkedJourney()) {
            $proposalLinkedJourney->setProposalLinkedJourney($newProposalLinkedJourney);
        }
        
        return $this;
    }
    
    public function getProposalOrigin(): ?self
    {
        return $this->proposalOrigin;
    }
    
    public function setProposalOrigin(?self $proposalOrigin): self
    {
        $this->proposalOrigin = $proposalOrigin;
        
        // set (or unset) the owning side of the relation if necessary
        $newProposalOrigin = $proposalOrigind === null ? null : $this;
        if ($newProposalOrigin !== $proposalOrigin->getProposalOrigin()) {
            $proposalOrigin->setProposalOrigin($newProposalOrigin);
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
     * @return Collection|Point[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setProposal($this);
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getProposal() === $this) {
                $point->setProposal(null);
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

    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }
    
    public function getStartLocality ()
    {
        return $this->startLocality;
    }

    public function setStartLocality ($startLocality)
    {
        $this->startLocality = $startLocality;
    }
    
    public function getDestinationLocality ()
    {
        return $this->destinationLocality;
    }

    public function setDestinationLocality ($destinationLocality)
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