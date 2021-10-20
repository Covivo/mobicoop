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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An article.
 */
class Article implements ResourceInterface, \JsonSerializable
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    const NB_EXTERNAL_ARTICLES_DEFAULT = 3;
    
    const RESOURCE_NAME = "pages";

    const CONTEXT_HOME = "home";
    const CONTEXT_INTERNAL= "internal";

    /**
     * @var int The id of this article.
     */
    private $id;

    /**
     * @var string|null The iri of this article.
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string The title of the article.
     *
     * @Groups({"post","put"})
     */
    private $title;

    /**
     * @var string The description of the article.
     *
     * @Groups({"post","put"})
     */
    private $description;

    /**
     * @var string The image of the article.
     *
     * @Groups({"post","put"})
     */
    private $image;

    /**
     * @var string The code of the article iFrame if it's displayed from an external source
     *
     */
    private $iFrame;
    
    /**
     * @var string The pubDate of the article.
     *
     * @Groups({"post","put"})
     */
    private $pubDate;

    /**
     * @var string The link of the article.
     *
     * @Groups({"post","put"})
     */
    private $link;

    /**
     * @var int The status of publication of the article.
     *
     * @Groups({"post","put"})
     */
    private $status;

    /**
     * @var Section[] The sections of the article.
     */
    private $sections;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/articles/".$id);
        }
        $this->sections = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
    
    public function setImage(?string $image): self
    {
        $this->image = $image;
        
        return $this;
    }

    public function getIFrame(): ?string
    {
        return $this->iFrame;
    }
    
    public function setIFrame(?string $iFrame): self
    {
        $this->iFrame = $iFrame;
        
        return $this;
    }

    public function getPubDate(): ?string
    {
        return $this->pubDate;
    }
    
    public function setPubDate(?string $pubDate): self
    {
        $this->pubDate = $pubDate;
        
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }
    
    public function setLink(?string $link): self
    {
        $this->link = $link;
        
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

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'sections' => $this->getSections(),
            'description' => $this->getDescription(),
            'image' => $this->getImage(),
            'iframe' => $this->getIFrame(),
            'pubDate' => $this->getPubDate(),
            'link' => $this->getLink(),
        ];
    }
}
