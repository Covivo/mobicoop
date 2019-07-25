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
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Carpool\Controller\AskPost;

/**
 * Carpooling : ask from/to a driver and/or a passenger (after a matching between an offer and a request).
 *
 * @ORM\Entity
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
 *              "path"="/asks",
 *              "controller"=AskPost::class,
 *          },
 *      },
 *      itemOperations={"get","put","delete"}
 * )
 */
class Ask
{
    const STATUS_INITIATED = 1;
    const STATUS_PENDING = 2;
    const STATUS_ACCEPTED = 3;
    const STATUS_DECLINED = 4;
    
    /**
     * @var int The id of this ask.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread"})
     */
    private $id;

    /**
     * @var int Ask status (1 = initiated; 2 = pending, 3 = accepted; 4 = declined).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads","thread"})
     */
    private $status;

    /**
     * @var int The ask type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads","thread"})
     */
    private $type;

    /**
     * @var \DateTimeInterface Creation date of the solicitation.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"threads","thread"})
     */
    private $createdDate;

    /**
     * @var User The user that creates the ask.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="asks")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Matching The matching at the origin of the ask.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching", inversedBy="asks")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $matching;

    /**
     * @var Ask|null The linked ask.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Ask")
     * @Groups({"read","threads","thread"})
     * @MaxDepth(1)
     */
    private $askLinked;

    /**
     * @var Criteria The criteria applied to the ask.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $criteria;
    
    /**
     * @var ArrayCollection The waypoints of the ask.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="ask", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $waypoints;

    /**
     * @var ArrayCollection The ask history items linked with the ask.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\AskHistory", mappedBy="ask", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $askHistories;
    
    public function __construct()
    {
        $this->waypoints = new ArrayCollection();
        $this->askHistories = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMatching(): Matching
    {
        return $this->matching;
    }

    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;

        return $this;
    }

    public function getAskLinked(): ?self
    {
        return $this->askLinked;
    }

    public function setAskLinked(?self $askLinked): self
    {
        $this->askLinked = $askLinked;

        // set (or unset) the owning side of the relation if necessary
        $newAskLinked = $askLinked === null ? null : $this;
        if ($newAskLinked !== $askLinked->getAsklLinked()) {
            $askLinked->setAskLinked($newAskLinked);
        }

        return $this;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;

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
            $waypoint->setAsk($this);
        }
        
        return $this;
    }
    
    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getAsk() === $this) {
                $waypoint->setAsk(null);
            }
        }
        
        return $this;
    }

    public function getAskHistories()
    {
        return $this->askHistories->getValues();
    }
    
    public function addAskHistory(AskHistory $askHistory): self
    {
        if (!$this->askHistories->contains($askHistory)) {
            $this->askHistories[] = $askHistory;
            $askHistory->setAsk($this);
        }
        
        return $this;
    }
    
    public function removeAskHistory(AskHistory $askHistory): self
    {
        if ($this->askHistories->contains($askHistory)) {
            $this->askHistories->removeElement($askHistory);
            // set the owning side to null (unless already changed)
            if ($askHistory->getAsk() === $this) {
                $askHistory->setAsk(null);
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
}
