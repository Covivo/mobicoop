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
use App\Community\Entity\Community;
use App\User\Entity\User;
use App\Communication\Entity\Notified;

/**
 * Carpooling : proposal (offer from a driver / request from a passenger).
 * Note : force eager is set to false to avoid max number of nested relations (can occure despite of maxdepth... https://github.com/api-platform/core/issues/1910)
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "path"="/proposals",
 *              "controller"=ProposalPost::class,
 *          },
 *          "simple_search"={
 *              "method"="GET",
 *              "path"="/proposals/search",
 *              "normalization_context"={"groups"={"results"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "origin_latitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the origin point"
 *                      },
 *                      {
 *                          "name" = "origin_longitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the origin point"
 *                      },
 *                      {
 *                          "name" = "destination_latitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the destination point"
 *                      },
 *                      {
 *                          "name" = "destination_longitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the destination point"
 *                      },
 *                      {
 *                          "name" = "frequency",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "integer",
 *                          "description" = "The frequency of the trip (1=punctual, 2=regular; for regular trips)"
 *                      },
 *                      {
 *                          "name" = "date",
 *                          "in" = "query",
 *                          "type" = "string",
 *                          "format" = "date-time",
 *                          "description" = "The date and time of the trip for a punctual trip, the start date for regular trips (on RFC3339 format)"
 *                      },
 *                      {
 *                          "name" = "useTime",
 *                          "in" = "query",
 *                          "type" = "boolean",
 *                          "description" = "True to use the time part of the date, false to ignore the time part"
 *                      },
 *                      {
 *                          "name" = "strictDate",
 *                          "in" = "query",
 *                          "type" = "boolean",
 *                          "description" = "True to limit the search to the date, false to search even in the next days (only for punctual trip)"
 *                      },
 *                      {
 *                          "name" = "strictPunctual",
 *                          "in" = "query",
 *                          "type" = "boolean",
 *                          "description" = "True to search only in punctual trips for punctual search, false to search also in regular trips"
 *                      },
 *                      {
 *                          "name" = "strictRegular",
 *                          "in" = "query",
 *                          "type" = "boolean",
 *                          "description" = "True to search only in regular trips for regular search, false to search also in punctual trips"
 *                      },
 *                      {
 *                          "name" = "marginTime",
 *                          "in" = "query",
 *                          "type" = "integer",
 *                          "description" = "The margin time in seconds"
 *                      },
 *                      {
 *                          "name" = "regularLifeTime",
 *                          "in" = "query",
 *                          "type" = "integer",
 *                          "description" = "The lifetime of a regular proposal in years (default defined in env variable)"
 *                      },
 *                      {
 *                          "name" = "userId",
 *                          "in" = "query",
 *                          "type" = "integer",
 *                          "description" = "The id of the user that makes the query"
 *                      },
 *                      {
 *                          "name" = "role",
 *                          "in" = "query",
 *                          "type" = "integer",
 *                          "description" = "The role of the user that makes the query (1=driver, 2=passenger, 3=both; default defined in env variable)"
 *                      },
 *                      {
 *                          "name" = "type",
 *                          "in" = "query",
 *                          "type" = "integer",
 *                          "description" = "The type of the trip (1=one way, 2=return trip; default defined in env variable)"
 *                      },
 *                      {
 *                          "name" = "anyRouteAsPassenger",
 *                          "in" = "query",
 *                          "type" = "boolean",
 *                          "description" = "True if the passenger accepts any route (not implemented yet; default defined in env variable)"
 *                      }
 *                  }
 *              }
 *          }
 *      },
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(NumericFilter::class, properties={"proposalType"})
 * @ApiFilter(DateFilter::class, properties={"criteria.fromDate"})
 */
class Proposal
{
    const DEFAULT_ID = 999999999999;

    const TYPE_ONE_WAY = 1;
    const TYPE_OUTWARD = 2;
    const TYPE_RETURN = 3;
    
    /**
     * @var int The id of this proposal.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","results","threads","thread"})
     */
    private $id;

    /**
     * @var int The proposal type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","results","write","threads","thread"})
     */
    private $type;
    
    /**
     * @var string A comment about the proposal.
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read","write","results","threads","thread"})
     */
    private $comment;

    /**
     * @var \DateTimeInterface Creation date of the proposal.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","threads","thread"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the proposal.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","threads","thread"})
     */
    private $updatedDate;

    /**
     * @var Proposal|null Linked proposal for a round trip (return or outward journey).
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Proposal", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","results","write"})
     */
    private $proposalLinked;
    
    /**
     * @var User User for whom the proposal is submitted (in general the user itself, except when it is a "posting for").
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="proposals")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","results","write"})
     */
    private $user;

    /**
     * @var User|null User that create the proposal for another user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="proposalsDelegate")
     * @Groups({"read","write"})
     */
    private $userDelegate;

    /**
     * @var ArrayCollection The waypoints of the proposal.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","results","write"})
     */
    private $waypoints;
    
    /**
     * @var ArrayCollection|null The travel modes accepted if the proposal is a request.
     *
     * @ORM\ManyToMany(targetEntity="\App\Travel\Entity\TravelMode")
     * @Groups({"read","write"})
     */
    private $travelModes;

    /**
     * @var ArrayCollection|null The communities related to the proposal.
     *
     * @ORM\ManyToMany(targetEntity="\App\Community\Entity\Community", inversedBy="proposals")
     * @Groups({"read","write"})
     */
    private $communities;

    /**
     * @var ArrayCollection|null The matching of the proposal (if proposal is an offer).
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Matching", mappedBy="proposalOffer", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","results"})
     * @MaxDepth(1)
     */
    private $matchingOffers;

    /**
     * @var ArrayCollection|null The matching of the proposal (if proposal is a request).
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Matching", mappedBy="proposalRequest", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","results"})
     */
    private $matchingRequests;

    /**
     * @var Criteria The criteria applied to the proposal.
     * Note :
     * The criteria is set as a nullable column, BUT it is in fact MANDATORY.
     * It is set as nullable because the owning side of a one-to-one association is saved first, so the inverse side does not exist yet.
     * Other solution : make the proposal as the inverse side, and the criteria as the owning side.
     * But it is not acceptable as a criteria can be related with other entities (ask and matching) so we would have multiple nullable foreign keys.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", inversedBy="proposal", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups({"read","results","write","thread"})
     */
    private $criteria;
    
    /**
     * @var ArrayCollection The individual stops of the proposal.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\IndividualStop", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read"})
     */
    private $individualStops;

    /**
     * @var ArrayCollection|null The notifications sent for the proposal.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $notifieds;

    /**
     * @var Proposal|null The proposal we know that already matched by this new proposal
     * @Groups({"read","write"})
     */
    private $matchedProposal;
        
    public function __construct($id=null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->communities = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
        $this->notifieds = new ArrayCollection();
    }
    
    public function __clone()
    {
        // when we clone a Proposal we keep only the basic properties, we re-initialize all the collections
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->communities = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
        $this->notifieds = new ArrayCollection();
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
    
    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        
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

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

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

    public function getUserDelegate(): ?User
    {
        return $this->userDelegate;
    }

    public function setUserDelegate(?User $userDelegate): self
    {
        $this->userDelegate = $userDelegate;

        return $this;
    }

    public function getWaypoints()
    {
        return $this->waypoints->getValues();
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
    
    public function getTravelModes()
    {
        return $this->travelModes->getValues();
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

    public function getCommunities()
    {
        return $this->communities->getValues();
    }
    
    public function addCommunity(Community $community): self
    {
        if (!$this->communities->contains($community)) {
            $this->communities[] = $community;
        }
        
        return $this;
    }
    
    public function removeCommunity(Community $community): self
    {
        if ($this->communities->contains($community)) {
            $this->communities->removeElement($community);
        }
        
        return $this;
    }
    
    public function getMatchingRequests()
    {
        return $this->matchingRequests->getValues();
    }

    public function addMatchingRequest(Matching $matchingRequest): self
    {
        if (!$this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests[] = $matchingRequest;
            $matchingRequest->setProposalRequest($this);
        }

        return $this;
    }

    public function removeMatchingRequest(Matching $matchingRequest): self
    {
        if ($this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests->removeElement($matchingRequest);
            // set the owning side to null (unless already changed)
            if ($matchingRequest->getProposalRequest() === $this) {
                $matchingRequest->setProposalRequest(null);
            }
        }

        return $this;
    }
    
    public function getMatchingOffers()
    {
        return $this->matchingOffers->getValues();
    }
    
    public function addMatchingOffer(Matching $matchingOffer): self
    {
        if (!$this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers[] = $matchingOffer;
            $matchingOffer->setProposalOffer($this);
        }
        
        return $this;
    }
    
    public function removeMatchingOffer(Matching $matchingOffer): self
    {
        if ($this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers->removeElement($matchingOffer);
            // set the owning side to null (unless already changed)
            if ($matchingOffer->getProposalOffer() === $this) {
                $matchingOffer->setProposalOffer(null);
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
        if ($criteria->getProposal() !== $this) {
            $criteria->setProposal($this);
        }
        $this->criteria = $criteria;
        
        return $this;
    }
    
    public function getIndividualStops()
    {
        return $this->individualStops->getValues();
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

    public function getNotifieds()
    {
        return $this->notifieds->getValues();
    }
    
    public function addNotified(Notified $notified): self
    {
        if (!$this->notifieds->contains($notified)) {
            $this->notifieds[] = $notified;
            $notified->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeNotified(Notified $notified): self
    {
        if ($this->notifieds->contains($notified)) {
            $this->notifieds->removeElement($notified);
            // set the owning side to null (unless already changed)
            if ($notified->getProposal() === $this) {
                $notified->setProposal(null);
            }
        }
        
        return $this;
    }
    
    public function getMatchedProposal(): ?Proposal
    {
        return $this->matchedProposal;
    }

    public function setMatchedProposal(?Proposal $matchedProposal): self
    {
        $this->matchedProposal = $matchedProposal;

        return $this;
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

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
