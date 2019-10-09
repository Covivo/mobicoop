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

/**
 * Carpooling : matching between an offer and a request.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
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
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="matchingOffers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","results","threads","thread"})
     * @MaxDepth(1)
     */
    private $proposalOffer;

    /**
     * @var Proposal The request proposal.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="matchingRequests")
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
     */
    private $criteria;

    /**
     * @var ArrayCollection The asks made for this matching.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="matching", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"read"})
     */
    private $asks;

    /**
     * @var ArrayCollection The waypoints of the proposal.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="matching", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write","threads","thread"})
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
