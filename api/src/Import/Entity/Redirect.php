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

namespace App\Import\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A list of redirections.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"originUri": "exact"})
 */
class Redirect
{
    const TYPE_COMMUNITY = 0;
    const TYPE_EVENT = 1;

    /**
     * @var int The id of this redirection.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The original URI.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $originUri;

    /**
     * @var int Redirection type.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $type;

    /**
     * @var string The language.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $language;

    /**
     * @var int Destination id.
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $destinationId;

    /**
     * @var \DateTimeInterface Creation date of the user import.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Update date of the user import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $updatedDate;

    /**
     * @var string Destination complement to send to the requester (eg. a name related to the object).
     * @Groups({"read","write"})
     */
    private $destinationComplement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginUri(): string
    {
        return $this->originUri;
    }

    public function setOriginUri(string $originUri): self
    {
        $this->originUri = $originUri;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getDestinationId(): int
    {
        return $this->destinationId;
    }

    public function setDestinationId(int $destinationId): self
    {
        $this->destinationId = $destinationId;

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

    public function getDestinationComplement(): string
    {
        return $this->destinationComplement;
    }

    public function setDestinationComplement(string $destinationComplement): self
    {
        $this->destinationComplement = $destinationComplement;

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
