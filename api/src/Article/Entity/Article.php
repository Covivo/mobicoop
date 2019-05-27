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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;

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
class Article
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    
    /**
     * @var int The id of this article.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
            
    /**
     * @var string The title of the article.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $title;

    /**
     * @var int The status of publication of the article.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var ArrayCollection The sections of the article.
     *
     * @ORM\OneToMany(targetEntity="\App\Article\Entity\Section", mappedBy="article", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
            
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        
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

    public function getSections()
    {
        return $this->sections->getValues();
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setArticle($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getArticle() === $this) {
                $section->setArticle(null);
            }
        }

        return $this;
    }
}
