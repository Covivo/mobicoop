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

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Carpooling : ask from/to a driver and/or a passenger (after a matching between an offer and a request).
 */
class Ask implements ResourceInterface
{
    /**
     * @var int The id of this ask.
     * @Groups({"post"})
     */
    private $id;

    /**
     * @var string|null The iri of this ask.
     * @Groups({"post"})
     */
    private $iri;

    /**
     * @var int Ask status (0 = waiting; 1 = accepted; 2 = declined).
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $status;

    /**
     * @var int The ask type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $type;

    /**
     * @var \DateTimeInterface Creation date of the solicitation.
     * @Groups({"post"})
     */
    private $createdDate;

    /**
     * @var User The user that creates the ask.
     *
     * @Assert\NotBlank
     * @Groups({"post"})
     */
    private $user;

    /**
     * @var Matching The matching at the origin of the ask.
     *
     * @Assert\NotBlank
     * @Groups({"post"})
     */
    private $matching;

    /**
     * @var Ask|null The linked ask.
     * @Groups({"post"})
     */
    private $askLinked;

    /**
     * @var Criteria The criteria applied to the ask.
     *
     * @Assert\NotBlank
     * @Groups({"post"})
     */
    private $criteria;
    
    /**
     * @var Waypoint[] The waypoints of the ask.
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $waypoints;
    
    public function __construct()
    {
        $this->waypoints = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
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

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMatching(): Matching
    {
        return $this->matching;
    }

    public function setMatching(Matching $matching): self
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
}
