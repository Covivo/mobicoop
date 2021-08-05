<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

use App\Event\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use App\Action\Entity\Log;
use App\App\Entity\App;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Travel\Entity\TravelMode;
use App\Community\Entity\Community;
use App\User\Entity\User;
use App\Communication\Entity\Notified;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\Subject;

/**
 * Carpooling : proposal (offer from a driver / request from a passenger).
 * Note : force eager is set to false to avoid max number of nested relations (can occure despite of maxdepth... https://github.com/api-platform/core/issues/1910)
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","results"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(NumericFilter::class, properties={"proposalType"})
 * @ApiFilter(BooleanFilter::class, properties={"private"})
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
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read","write","results","threads","thread"})
     */
    private $comment;

    /**
     * @var boolean Exposed proposal.
     * An exposed proposal is a search proposal that can be publicly visible via an url link.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $exposed;

    /**
     * @var string|null External ID : used to mask the real id for external requests.
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"read","write","results","threads","thread"})
     */
    private $externalId;

    /**
     * @var boolean Proposal well suited for SEO optimization.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $seo;

    /**
     * @var boolean Dynamic proposal.
     * A dynamic proposal is a real-time proposal : used for dynamic carpooling.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $dynamic;

    /**
     * @var boolean Active proposal.
     * Used for dynamic carpooling, only active proposal can be matched.
     * A passenger proposal is set to inactive when an ask is accepted, a driver proposal is set to inactive when no more passenger can be involved.
     * An inactive ad can still be updated, to keep the positions till the destination.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $active;

    /**
     * @var boolean Finished proposal.
     * Used for dynamic carpooling, only unfinished proposal can be matched.
     * An ad is set to finished when it is manually stopped, or when the destination is reached.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $finished;

    /**
     * @var boolean Private proposal.
     * A private proposal can't be the found in the result of a search.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $private;

    /**
     * @var boolean Paused proposal.
     * A paused proposal can't be the found in the result of a search, and can be unpaused at any moment.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $paused;

    /**
     * @var boolean Proposal without destination.
     * Used for solidary.
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $noDestination;

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
     * @MaxDepth(1)
     */
    private $proposalLinked;
    
    /**
     * @var User|null User for whom the proposal is submitted (in general the user itself, except when it is a "posting for").
     * Can be null for an anonymous search.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="proposals")
     * @Groups({"read","results","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var User|null User that create the proposal for another user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="proposalsDelegate")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $userDelegate;

    /**
     * @var App|null App that create the user.
     *
     * @ORM\ManyToOne(targetEntity="\App\App\Entity\App")
     * @Groups({"readUser","write"})
     * @MaxDepth(1)
     */
    private $appDelegate;
    
    /**
     * @var ArrayCollection The waypoints of the proposal.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="proposal", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $waypoints;
    
    /**
     * @var ArrayCollection|null The travel modes accepted if the proposal is a request.
     *
     * @ORM\ManyToMany(targetEntity="\App\Travel\Entity\TravelMode")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $travelModes;

    /**
     * @var ArrayCollection|null The communities related to the proposal.
     *
     * @ORM\ManyToMany(targetEntity="\App\Community\Entity\Community", inversedBy="proposals")
     * @Groups({"read","results","write"})
     * @MaxDepth(1)
     */
    private $communities;

    /**
     * @var ArrayCollection|null The matchings of the proposal (if proposal is a request).
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Matching", mappedBy="proposalRequest", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","results"})
     * @MaxDepth(1)
     */
    private $matchingOffers;

    /**
     * @var ArrayCollection|null The matching of the proposal (if proposal is an offer).
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Matching", mappedBy="proposalOffer", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","results"})
     * @MaxDepth(1)
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
     * @MaxDepth(1)
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
     * @var Matching|null The matching of the linked proposal (used for regular return trips).
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $matchingLinked;

    /**
     * @var Ask|null The ask of the linked proposal (used for regular return trips).
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $askLinked;

    /**
     * @var array|null The carpool results for the proposal.
     * Results are taken from the matchings, but returned in a more user-friendly way.
     * @Groups("results")
     */
    private $results;

    /**
     * @var Event related for the proposal
     *
     * @ORM\ManyToOne(targetEntity="App\Event\Entity\Event", inversedBy="proposals")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $event;

    /**
     * @var Position The last position given for dynamic carpooling.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Position", mappedBy="proposal", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read","results","write","thread"})
     */
    private $position;

    /**
     * @var string The external origin of this proposal (i.e. for an RDEX request we store the public api key)
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $external;

    /**
     * @var Subject A Proposal can be linked to a specific Subject
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Subject", inversedBy="proposals")
     * @MaxDepth(1)
     * @Groups({"read","write"})
     */
    private $subject;

    /**
     * @var Solidary The solidary linked with this proposal
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\Solidary", mappedBy="proposal")
     */
    private $solidary;

    /**
     * @var bool Use search time or not
     */
    private $useTime;

    /**
     * @var ArrayCollection The logs linked with the Proposal.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="proposal", cascade={"remove"})
     */
    private $logs;

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
        $this->setPrivate(false);
        $this->setPaused(false);
        $this->setExposed(false);
        $this->results = [];
    }
    
    public function __clone()
    {
        // when we clone a Proposal we keep only the basic properties, we re-initialize all the collections
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
        $this->notifieds = new ArrayCollection();
        $this->results = [];
        $this->setProposalLinked(null);
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

    public function isExposed(): bool
    {
        return $this->exposed ? true : false;
    }

    public function setExposed(?bool $exposed): self
    {
        $this->exposed = $exposed;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(): self
    {
        $this->externalId = $this->generateRandomId(10);

        return $this;
    }

    public function isSeo(): bool
    {
        return $this->seo ? true : false;
    }

    public function setSeo(?bool $seo): self
    {
        $this->seo = $seo;

        return $this;
    }

    public function isDynamic(): bool
    {
        return $this->dynamic ? true : false;
    }

    public function setDynamic(?bool $dynamic): self
    {
        $this->dynamic = $dynamic;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active ? true : false;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished ? true : false;
    }

    public function setFinished(?bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private ? true : false;
    }

    public function setPrivate(?bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function isPaused(): bool
    {
        return $this->paused ? true : false;
    }

    public function setPaused(?bool $paused): self
    {
        $this->paused = $paused;

        return $this;
    }

    public function hasNoDestination(): bool
    {
        return $this->noDestination ? true : false;
    }

    public function setNoDestination(?bool $noDestination): self
    {
        $this->noDestination = $noDestination;

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
        if ($proposalLinked && $newProposalLinked !== $proposalLinked->getProposalLinked()) {
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

    public function getAppDelegate(): ?App
    {
        return $this->appDelegate;
    }

    public function setAppDelegate(?App $appDelegate): self
    {
        $this->appDelegate = $appDelegate;

        return $this;
    }

    public function getWaypoints()
    {
        return $this->waypoints->getValues();
    }

    public function getWaypointById(int $id): ?Waypoint
    {
        return array_search($id, array_column($this->getWaypoints(), 'id'));
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
    
    public function getMatchingOffers()
    {
        return $this->matchingOffers->getValues();
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
    
    public function getMatchingLinked(): ?Matching
    {
        return $this->matchingLinked;
    }

    public function setMatchingLinked(?Matching $matchingLinked): self
    {
        $this->matchingLinked = $matchingLinked;

        return $this;
    }

    public function getAskLinked(): ?Ask
    {
        return $this->askLinked;
    }

    public function setAskLinked(?Ask $askLinked): self
    {
        $this->askLinked = $askLinked;

        return $this;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): self
    {
        if ($position->getProposal() !== $this) {
            $position->setProposal($this);
        }
        $this->position = $position;

        return $this;
    }
        
    public function getExternal(): ?String
    {
        return $this->external;
    }

    public function setExternal(?string $external): self
    {
        $this->external = $external;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getUseTime(): bool
    {
        return $this->useTime ? true : false;
    }

    public function setUseTime(?bool $useTime): self
    {
        $this->useTime = $useTime;

        return $this;
    }

    /**
     * Generate random id
     *
     * @param integer $int  The length of the id
     * @return string The generated id
     */
    public function generateRandomId(int $int=15)
    {
        return bin2hex(random_bytes($int));
    }

    public function getLogs()
    {
        return $this->logs->getValues();
    }
    
    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getProposal() === $this) {
                $log->setProposal(null);
            }
        }
        
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


    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }

    public function setSolidary(Solidary $solidary): self
    {
        $this->solidary = $solidary;

        return $this;
    }
}
