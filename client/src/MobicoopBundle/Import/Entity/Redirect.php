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

namespace Mobicoop\Bundle\MobicoopBundle\Import\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * A list of redirections.
 */
class Redirect implements ResourceInterface
{
    const TYPE_COMMUNITY = 0;
    const TYPE_EVENT = 1;

    /**
     * @var int The id of this redirection.
     */
    private $id;

    /**
     * @var string|null The iri of this redirection.
     *
     * @Groups({"post","put"})
     */
    private $iri;
    
    /**
     * @var string The original URI.
     */
    private $originUri;

    /**
     * @var int Redirection type.
     */
    private $type;

    /**
     * @var string The language.
     */
    private $language;

    /**
     * @var int Destination id.
     */
    private $destinationId;

    /**
     * @var \DateTimeInterface Creation date of the user import.
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Update date of the user import.
     */
    private $updatedDate;

    /**
     * @var string Destination complement to send to the requester (eg. a name related to the object).
     */
    private $destinationComplement;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/redirects/".$id);
        }
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
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
}
