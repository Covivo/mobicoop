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
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Communication\Entity\Notified;
use App\Solidary\Entity\SolidaryMatching;

/**
 * Carpooling : matching between an offer and a request.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class Matching
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this matching.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread"})
     */
    private $id;

    /**
     * @var \DateTimeInterface Creation date of the matching.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","threads","thread"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the matching.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","threads","thread"})
     */
    private $updatedDate;

    /**
     * @var Proposal The offer proposal.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="matchingRequests")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $proposalOffer;

    /**
     * @var Proposal The request proposal.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="matchingOffers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $proposalRequest;

    /**
     * @var Criteria The criteria applied to this matching.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $criteria;

    /**
     * @var Matching|null Related matching for a round trip (return or outward journey).
     * Not persisted : used only to get the return trip information.
     */
    private $matchingRelated;

    /**
     * @var Matching|null Linked matching for return trip.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Matching", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","results","write"})
     * @MaxDepth(1)
     */
    private $matchingLinked;

    /**
     * @var Matching|null Opposite matching (if proposal and request can be switched, so if driver and passenger can switch roles).
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Matching", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","results","write"})
     * @MaxDepth(1)
     */
    private $matchingOpposite;

    /**
     * @var ArrayCollection The asks made for this matching.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="matching", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $asks;

    /**
     * @var ArrayCollection The waypoints of the proposal.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="matching", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $waypoints;

    /**
     * @var ArrayCollection|null The notifications sent for the matching.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="matching", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $notifieds;

    /**
     * @var array The filters returned to the user. The user can then filter and sort the results.
     * @Groups({"read","write","results"})
     */
    private $filters;

    /**
     * @var int|null The original distance of the driver in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $originalDistance;

    /**
     * @var int|null The accepted detour distance of the driver in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $acceptedDetourDistance;

    /**
     * @var int|null The new distance of the driver including the detour in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $newDistance;

    /**
     * @var int|null The detour distance of the driver in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDistance;

    /**
     * @var float|null The detour distance of the driver in percentage of the original distance.
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDistancePercent;

    /**
     * @var int|null The original duration of the driver in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $originalDuration;

    /**
     * @var int|null The accepted detour duration of the driver in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $acceptedDetourDuration;

    /**
     * @var int|null The new duration of the driver including the detour in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $newDuration;

    /**
     * @var int|null The detour duration of the driver in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDuration;

    /**
     * @var int|null The detour duration of the driver in percentage of the original duration.
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDurationPercent;

    /**
     * @var int|null The common distance in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $commonDistance;

    /**
     * @var int|null The duration till the pick up of the passenger in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $pickUpDuration;

    /**
     * @var int|null The duration till the dropoff of the passenger in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $dropOffDuration;

    /**
     * @var SolidaryMatching|null The solidary matching if there is any
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryMatching", mappedBy="matching", cascade={"persist","remove"})
     * @Groups({"read","results",})
     * @MaxDepth(1)
     */
    private $solidaryMatching;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->asks = new ArrayCollection();
        $this->waypoints = new ArrayCollection();
        $this->notifieds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMatchingRelated(): ?self
    {
        return $this->matchingRelated;
    }
    
    public function setMatchingRelated(?self $matchingRelated): self
    {
        $this->matchingRelated = $matchingRelated;

        if (!$this->getMatchingRelated()) {
            return $this;
        }
        
        // set (or unset) the owning side of the relation if necessary
        $newMatchingRelated = $matchingRelated === null ? null : $this;
        if ($newMatchingRelated !== $matchingRelated->getMatchingRelated()) {
            $matchingRelated->setMatchingRelated($newMatchingRelated);
        }
        
        return $this;
    }

    public function getMatchingLinked(): ?self
    {
        return $this->matchingLinked;
    }
    
    public function setMatchingLinked(?self $matchingLinked): self
    {
        $this->matchingLinked = $matchingLinked;
        
        if (!$this->getMatchingLinked()) {
            return $this;
        }

        // set (or unset) the owning side of the relation if necessary
        $newMatchingLinked = $matchingLinked === null ? null : $this;
        if ($newMatchingLinked !== $matchingLinked->getMatchingLinked()) {
            $matchingLinked->setMatchingLinked($newMatchingLinked);
        }
        
        return $this;
    }

    public function getMatchingOpposite(): ?self
    {
        return $this->matchingOpposite;
    }
    
    public function setMatchingOpposite(?self $matchingOpposite): self
    {
        $this->matchingOpposite = $matchingOpposite;

        if (!$this->getMatchingOpposite()) {
            return $this;
        }
        
        // set (or unset) the owning side of the relation if necessary
        $newMatchingOpposite = $matchingOpposite === null ? null : $this;
        if ($newMatchingOpposite !== $matchingOpposite->getMatchingOpposite()) {
            $matchingOpposite->setMatchingOpposite($newMatchingOpposite);
        }
        
        return $this;
    }

    public function getAsks()
    {
        return $this->asks->getValues();
    }

    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks[] = $ask;
            $ask->setMatching($this);
        }

        return $this;
    }

    public function removeAsk(Ask $ask): self
    {
        if ($this->asks->contains($ask)) {
            $this->asks->removeElement($ask);
            // set the owning side to null (unless already changed)
            if ($ask->getMatching() === $this) {
                $ask->setMatching(null);
            }
        }

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

    public function getNotifieds()
    {
        return $this->notifieds->getValues();
    }
    
    public function addNotified(Notified $notified): self
    {
        if (!$this->notifieds->contains($notified)) {
            $this->notifieds[] = $notified;
            $notified->setMatching($this);
        }
        
        return $this;
    }
    
    public function removeNotified(Notified $notified): self
    {
        if ($this->notifieds->contains($notified)) {
            $this->notifieds->removeElement($notified);
            // set the owning side to null (unless already changed)
            if ($notified->getMatching() === $this) {
                $notified->setMatching(null);
            }
        }
        
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

    public function getOriginalDistance(): ?int
    {
        return $this->originalDistance;
    }

    public function setOriginalDistance(int $originalDistance): self
    {
        $this->originalDistance = $originalDistance;

        return $this;
    }

    public function getAcceptedDetourDistance(): ?int
    {
        return $this->acceptedDetourDistance;
    }

    public function setAcceptedDetourDistance(int $acceptedDetourDistance): self
    {
        $this->acceptedDetourDistance = $acceptedDetourDistance;

        return $this;
    }

    public function getNewDistance(): ?int
    {
        return $this->newDistance;
    }

    public function setNewDistance(int $newDistance): self
    {
        $this->newDistance = $newDistance;

        return $this;
    }

    public function getDetourDistance(): ?int
    {
        return $this->detourDistance;
    }

    public function setDetourDistance(int $detourDistance): self
    {
        $this->detourDistance = $detourDistance;

        return $this;
    }

    public function getDetourDistancePercent(): ?float
    {
        return $this->detourDistancePercent;
    }

    public function setDetourDistancePercent(float $detourDistancePercent): self
    {
        $this->detourDistancePercent = $detourDistancePercent;

        return $this;
    }

    public function getOriginalDuration(): ?int
    {
        return $this->originalDuration;
    }

    public function setOriginalDuration(int $originalDuration): self
    {
        $this->originalDuration = $originalDuration;

        return $this;
    }

    public function getAcceptedDetourDuration(): ?int
    {
        return $this->acceptedDetourDuration;
    }

    public function setAcceptedDetourDuration(int $acceptedDetourDuration): self
    {
        $this->acceptedDetourDuration = $acceptedDetourDuration;

        return $this;
    }

    public function getNewDuration(): ?int
    {
        return $this->newDuration;
    }

    public function setNewDuration(int $newDuration): self
    {
        $this->newDuration = $newDuration;

        return $this;
    }

    public function getDetourDuration(): ?int
    {
        return $this->detourDuration;
    }

    public function setDetourDuration(int $detourDuration): self
    {
        $this->detourDuration = $detourDuration;

        return $this;
    }

    public function getDetourDurationPercent(): ?float
    {
        return $this->detourDurationPercent;
    }

    public function setDetourDurationPercent(float $detourDurationPercent): self
    {
        $this->detourDurationPercent = $detourDurationPercent;

        return $this;
    }

    public function getCommonDistance(): ?int
    {
        return $this->commonDistance;
    }

    public function setCommonDistance(int $commonDistance): self
    {
        $this->commonDistance = $commonDistance;

        return $this;
    }

    public function getPickUpDuration(): ?int
    {
        return $this->pickUpDuration;
    }

    public function setPickUpDuration(int $pickUpDuration): self
    {
        $this->pickUpDuration = $pickUpDuration;

        return $this;
    }

    public function getDropOffDuration(): ?int
    {
        return $this->dropOffDuration;
    }

    public function setDropOffDuration(int $dropOffDuration): self
    {
        $this->dropOffDuration = $dropOffDuration;

        return $this;
    }

    public function getSolidaryMatching(): ?SolidaryMatching
    {
        return $this->solidaryMatching;
    }

    public function setSolidaryMatching(SolidaryMatching $solidaryMatching): self
    {
        $this->solidaryMatching = $solidaryMatching;

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
