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

namespace App\Carpool\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Action\Entity\Log;
use App\Communication\Entity\Notified;
use App\Solidary\Entity\SolidaryMatching;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : matching between an offer and a request.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","readSolidary"}, "enable_max_depth"="true"},
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
 *          }
 *      }
 * )
 */
class Matching
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of this matching
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread","readSolidary"})
     */
    private $id;

    /**
     * @var \DateTimeInterface creation date of the matching
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","threads","thread"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the matching
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","threads","thread"})
     */
    private $updatedDate;

    /**
     * @var Proposal the offer proposal
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="matchingRequests", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $proposalOffer;

    /**
     * @var Proposal the request proposal
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="matchingOffers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $proposalRequest;

    /**
     * @var Criteria the criteria applied to this matching
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $criteria;

    /**
     * @var null|Matching Related matching for a round trip (return or outward journey).
     *                    Not persisted : used only to get the return trip information.
     */
    private $matchingRelated;

    /**
     * @var null|Matching linked matching for return trip
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Matching", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","results","write"})
     * @MaxDepth(1)
     */
    private $matchingLinked;

    /**
     * @var null|Matching opposite matching (if proposal and request can be switched, so if driver and passenger can switch roles)
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Matching", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","results","write"})
     * @MaxDepth(1)
     */
    private $matchingOpposite;

    /**
     * @var ArrayCollection the asks made for this matching
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="matching")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $asks;

    /**
     * @var ArrayCollection the waypoints of the matching
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="matching", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $waypoints;

    /**
     * @var null|ArrayCollection the notifications sent for the matching
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="matching", cascade={"persist"})
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
     * @var null|int the original distance of the driver in metres
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $originalDistance;

    /**
     * @var null|int the accepted detour distance of the driver in metres
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $acceptedDetourDistance;

    /**
     * @var null|int the new distance of the driver including the detour in metres
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $newDistance;

    /**
     * @var null|int the detour distance of the driver in metres
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDistance;

    /**
     * @var null|float the detour distance of the driver in percentage of the original distance
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDistancePercent;

    /**
     * @var null|int the original duration of the driver in seconds
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $originalDuration;

    /**
     * @var null|int the accepted detour duration of the driver in seconds
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $acceptedDetourDuration;

    /**
     * @var null|int the new duration of the driver including the detour in seconds
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $newDuration;

    /**
     * @var null|int the detour duration of the driver in seconds
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDuration;

    /**
     * @var null|int the detour duration of the driver in percentage of the original duration
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Groups({"read","write","results"})
     */
    private $detourDurationPercent;

    /**
     * @var null|int the common distance in metres
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $commonDistance;

    /**
     * @var null|int the duration till the pick up of the passenger in seconds
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $pickUpDuration;

    /**
     * @var null|int the duration till the dropoff of the passenger in seconds
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","results"})
     */
    private $dropOffDuration;

    /**
     * @var null|SolidaryMatching The solidary matching if there is any
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryMatching", mappedBy="matching", cascade={"persist"})
     * @Groups({"read","results","readSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryMatching;

    /**
     * @var ArrayCollection the logs linked with the Matching
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="matching")
     */
    private $logs;

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
        $newMatchingRelated = null === $matchingRelated ? null : $this;
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
        $newMatchingLinked = null === $matchingLinked ? null : $this;
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
        $newMatchingOpposite = null === $matchingOpposite ? null : $this;
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

    public function getLogs()
    {
        return $this->logs->getValues();
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setMatching($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getMatching() === $this) {
                $log->setMatching(null);
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
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
