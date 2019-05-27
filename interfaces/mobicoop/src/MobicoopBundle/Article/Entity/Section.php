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

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A section of an article.
 */
class Section implements Resource
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    
    /**
     * @var int The id of this section.
     */
    private $id;
            
    /**
     * @var string The title of the section.
     *
     * @Groups({"post","put"})
     */
    private $title;

    /**
     * @var string The subtitle of the section.
     *
     * @Groups({"post","put"})
     */
    private $subTitle;

    /**
     * @var int The position of the section in the article.
     *
     * @Groups({"post","put"})
     */
    private $position;

    /**
     * @var int The status of publication of the section.
     *
     * @Groups({"post","put"})
     */
    private $status;

    /**
     * @var Article|null The article related to the section.
     *
     * @Groups({"post","put"})
     */
    private $article;

    /**
     * @var ArrayCollection The paragraphs of the section.
     *
     * @Groups({"post","put"})
     */
    private $paragraphs;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/sections/".$id);
        }
        $this->paragraphs = new ArrayCollection();
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

    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus(?int $status)
    {
        $this->status = $status;
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
