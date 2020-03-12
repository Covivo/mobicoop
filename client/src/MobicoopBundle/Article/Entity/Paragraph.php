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

namespace Mobicoop\Bundle\MobicoopBundle\Article\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A paragraph of a section.
 */
class Paragraph implements ResourceInterface, \JsonSerializable
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    
    /**
     * @var int The id of this paragraph.
     */
    private $id;

    /**
     * @var string|null The iri of this paragraph.
     *
     * @Groups({"post","put"})
     */
    private $iri;
            
    /**
     * @var string The text of the paragraph.
     * @Groups({"post","put"})
     */
    private $text;

    /**
     * @var int The position of the paragraph in the section.
     * @Groups({"post","put"})
     */
    private $position;

    /**
     * @var int The status of publication of the paragraph.
     *
     * @Groups({"post","put"})
     */
    private $status;

    /**
     * @var Section|null The section related to the paragraph.
     * @Groups({"post","put"})
     */
    private $section;

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
            
    public function getText(): ?string
    {
        return $this->text;
    }
    
    public function setText(?string $text): self
    {
        $this->text = $text;
        
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(?int $position): self
    {
        $this->position = $position;
        
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus(?int $status)
    {
        $this->status = $status;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }
    
    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id' => $this->getId(),
            'text' => $this->getText(),
        ];
    }
}
