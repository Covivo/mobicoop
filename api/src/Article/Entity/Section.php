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
use App\Geography\Entity\Article;

/**
 * A section of an article.
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
class Section
{
    
    /**
     * @var int The id of this section.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
            
    /**
     * @var string The title of the section.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $title;

    /**
     * @var string The subtitle of the section.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $subTitle;

    /**
     * @var int The position of the section in the article.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var Article|null The article related to the section.
     *
     * @ORM\ManyToOne(targetEntity="\App\Article\Entity\Article", inversedBy="sections")
     * @Groups({"read","write"})
     */
    private $article;

    /**
     * @var ArrayCollection The paragraphs of the section.
     *
     * @ORM\OneToMany(targetEntity="\App\Article\Entity\Paragraph", mappedBy="section", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     */
    private $paragraphs;

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

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }
    
    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;
        
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

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getParagraphs()
    {
        return $this->paragraphs->getValues();
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setSection($this);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        if ($this->paragraphs->contains($paragraph)) {
            $this->paragraphs->removeElement($paragraph);
            // set the owning side to null (unless already changed)
            if ($paragraph->getSection() === $this) {
                $paragraph->setSection(null);
            }
        }

        return $this;
    }

}
