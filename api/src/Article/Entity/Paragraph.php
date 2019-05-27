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

namespace App\Article\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;

/**
 * An article.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class Paragraph
{
    
    /**
     * @var int The id of this paragraph.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
            
    /**
     * @var string The text of the paragraph.
     *
     * @ORM\Column(type="text")
     * @Groups({"read","write"})
     */
    private $text;

    /**
     * @var int The position of the paragraph in the section.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var Section|null The section related to the paragraph.
     *
     * @ORM\ManyToOne(targetEntity="\App\Article\Entity\Section", inversedBy="paragraphs")
     * @Groups({"read","write"})
     */
    private $section;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setArticle(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }

}
