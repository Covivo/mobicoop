<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;

/**
 * Carpooling : result item for a search / ad post.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class ResultItem
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this result item.
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var Proposal The matching proposal.
     * @Groups("results")
     */
    private $proposal;

    /**
     * @var \DateTimeInterface The computed date and time for a punctual journey for the person who search / post.
     * @Groups("results")
     */
    private $date;

    /**
     * @var Address The origin address (the origin of the carpooler who search or post).
     * @Groups("results")
     */
    private $origin;

    /**
     * @var boolean True if the origin is the first waypoint of the journey.
     * @Groups("results")
     */

    private $originFirst;

    /**
     * @var Address The destination address (the destination of the carpooler who search or post).
     * @Groups("results")
     */
    private $destination;

    /**
     * @var boolean True if the destination is the last point of the journey.
     * @Groups("results")
     */
    private $destinationLast;

    /**
     * @var ArrayCollection The waypoints of the journey.
     * @Groups("results")
     */
    private $waypoints;

    /**
     * @var boolean|null The journey is available on mondays (if regular).
     * @Groups("results")
     */
    private $monCheck;

    /**
     * @var boolean|null The journey is available on tuesdays (if regular).
     * @Groups("results")
     */
    private $tueCheck;

    /**
     * @var boolean|null The journey is available on wednesdays (if regular).
     * @Groups("results")
     */
    private $wedCheck;

    /**
     * @var boolean|null The journey is available on thursdays (if regular).
     * @Groups("results")
     */
    private $thuCheck;

    /**
     * @var boolean|null The journey is available on fridays (if regular).
     * @Groups("results")
     */
    private $friCheck;

    /**
     * @var boolean|null The journey is available on saturdays (if regular).
     * @Groups("results")
     */
    private $satCheck;

    /**
     * @var boolean|null The journey is available on sundays (if regular).
     * @Groups("results")
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null Mondays computed starting time (if regular).
     * @Groups("results")
     */
    private $monTime;

    /**
     * @var \DateTimeInterface|null Tuesdays computed starting time (if regular).
     * @Groups("results")
     */
    private $tueTime;

    /**
     * @var \DateTimeInterface|null Wednesdays computed starting time (if regular).
     * @Groups("results")
     */
    private $wedTime;

    /**
     * @var \DateTimeInterface|null Thursdays computed starting time (if regular).
     * @Groups("results")
     */
    private $thuTime;

    /**
     * @var \DateTimeInterface|null Fridays computed starting time (if regular).
     * @Groups("results")
     */
    private $friTime;

    /**
     * @var \DateTimeInterface|null Saturdays computed starting time (if regular).
     * @Groups("results")
     */
    private $satTime;

    /**
     * @var \DateTimeInterface|null Sundays computed starting time (if regular).
     * @Groups("results")
     */
    private $sunTime;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->waypoints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }
    
    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;
        
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getOrigin(): ?Address
    {
        return $this->origin;
    }

    public function setOrigin(?Address $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function isOriginFirst(): ?bool
    {
        return $this->originFirst;
    }
    
    public function setOriginFirst(bool $isOriginFirst): self
    {
        $this->originFirst = $isOriginFirst;
        
        return $this;
    }

    public function getDestination(): ?Address
    {
        return $this->destination;
    }

    public function setDestination(?Address $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function isDestinationLast(): ?bool
    {
        return $this->destinationLast;
    }
    
    public function setDestinationLast(bool $isDestinationLast): self
    {
        $this->destinationLast = $isDestinationLast;
        
        return $this;
    }

    public function getWaypoints()
    {
        return $this->waypoints->getValues();
    }

    public function setWaypoints($waypoints): self
    {
        $this->waypoints = $waypoints;
        return $this;
    }

    public function isMonCheck(): ?bool
    {
        return $this->monCheck;
    }

    public function setMonCheck(?bool $monCheck): self
    {
        $this->monCheck = $monCheck;

        return $this;
    }

    public function isTueCheck(): ?bool
    {
        return $this->tueCheck;
    }

    public function setTueCheck(?bool $tueCheck): self
    {
        $this->tueCheck = $tueCheck;

        return $this;
    }

    public function isWedCheck(): ?bool
    {
        return $this->wedCheck;
    }

    public function setWedCheck(?bool $wedCheck): self
    {
        $this->wedCheck = $wedCheck;

        return $this;
    }

    public function isThuCheck(): ?bool
    {
        return $this->thuCheck;
    }

    public function setThuCheck(?bool $thuCheck): self
    {
        $this->thuCheck = $thuCheck;

        return $this;
    }

    public function isFriCheck(): ?bool
    {
        return $this->friCheck;
    }

    public function setFriCheck(?bool $friCheck): self
    {
        $this->friCheck = $friCheck;

        return $this;
    }

    public function isSatCheck(): ?bool
    {
        return $this->satCheck;
    }

    public function setSatCheck(?bool $satCheck): self
    {
        $this->satCheck = $satCheck;

        return $this;
    }

    public function isSunCheck(): ?bool
    {
        return $this->sunCheck;
    }

    public function setSunCheck(?bool $sunCheck): self
    {
        $this->sunCheck = $sunCheck;

        return $this;
    }

    public function getMonTime(): ?\DateTimeInterface
    {
        if ($this->monTime) {
            return \DateTime::createFromFormat('His', $this->monTime->format('His'));
        }
        return null;
    }

    public function setMonTime(?\DateTimeInterface $monTime): self
    {
        $this->monTime = $monTime;

        return $this;
    }

    public function getTueTime(): ?\DateTimeInterface
    {
        if ($this->tueTime) {
            return \DateTime::createFromFormat('His', $this->tueTime->format('His'));
        }
        return null;
    }

    public function setTueTime(?\DateTimeInterface $tueTime): self
    {
        $this->tueTime = $tueTime;

        return $this;
    }

    public function getWedTime(): ?\DateTimeInterface
    {
        if ($this->wedTime) {
            return \DateTime::createFromFormat('His', $this->wedTime->format('His'));
        }
        return null;
    }

    public function setWedTime(?\DateTimeInterface $wedTime): self
    {
        $this->wedTime = $wedTime;

        return $this;
    }

    public function getThuTime(): ?\DateTimeInterface
    {
        if ($this->thuTime) {
            return \DateTime::createFromFormat('His', $this->thuTime->format('His'));
        }
        return null;
    }

    public function setThuTime(?\DateTimeInterface $thuTime): self
    {
        $this->thuTime = $thuTime;

        return $this;
    }

    public function getFriTime(): ?\DateTimeInterface
    {
        if ($this->friTime) {
            return \DateTime::createFromFormat('His', $this->friTime->format('His'));
        }
        return null;
    }

    public function setFriTime(?\DateTimeInterface $friTime): self
    {
        $this->friTime = $friTime;

        return $this;
    }

    public function getSatTime(): ?\DateTimeInterface
    {
        if ($this->satTime) {
            return \DateTime::createFromFormat('His', $this->satTime->format('His'));
        }
        return null;
    }

    public function setSatTime(?\DateTimeInterface $satTime): self
    {
        $this->satTime = $satTime;

        return $this;
    }

    public function getSunTime(): ?\DateTimeInterface
    {
        if ($this->sunTime) {
            return \DateTime::createFromFormat('His', $this->sunTime->format('His'));
        }
        return null;
    }
    
    public function setSunTime(?\DateTimeInterface $sunTime): self
    {
        $this->sunTime = $sunTime;
        
        return $this;
    }
}
