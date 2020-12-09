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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Carpooling : an effective journey.
 *
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="IDX_ORIGIN", columns={"origin"})})
 * @ORM\Table(indexes={@ORM\Index(name="IDX_DESTINATION", columns={"destination"})})
 * @ORM\Table(indexes={@ORM\Index(name="IDX_ORIGIN_DESTINATION", columns={"origin","destination"})})
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readJourney"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeJourney"}}
 *      },
 *      collectionOperations={
 *          "get"
 *      },
 *      itemOperations={
 *          "get",
 *      }
 * )
 */
class Journey
{
    
    /**
     * @var int The id of this journey.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readJourney"})
     */
    private $id;

    /**
     * @var int The proposal id for this journey
     * @ORM\Column(type="integer")
     * @Groups({"readJourney","writeJourney"})
     */
    private $proposalId;
    
    /**
     * @var string The origin of the journey
     * @ORM\Column(type="string")
     * @Groups({"readJourney","writeJourney"})
     */
    private $origin;

    /**
     * @var string The destination of the journey
     * @ORM\Column(type="string")
     * @Groups({"readJourney","writeJourney"})
     */
    private $destination;

    /**
     * @var int The proposal frequency (1 = punctual; 2 = regular).
     * @ORM\Column(type="smallint")
     * @Groups({"readJourney","writeJourney"})
     */
    private $frequency;

    /**
     * @var \DateTimeInterface The starting date.
     *
     * @ORM\Column(type="date")
     * @Groups({"readJourney","writeJourney"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The end date.
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"readJourney","writeJourney"})
     */
    private $toDate;

    /**
     * @var \DateTimeInterface Creation date of the journey.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readJourney"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the journey.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposalId(): int
    {
        return $this->proposalId;
    }
    
    public function setProposalId(int $proposalId): self
    {
        $this->proposalId = $proposalId;

        return $this;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }
    
    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }
    
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(?\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

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
