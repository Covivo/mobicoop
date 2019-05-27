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
 * An article.
 */
class Article implements Resource
{
    
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
