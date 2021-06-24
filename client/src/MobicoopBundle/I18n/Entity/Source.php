<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\I18n\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mobicoop\Bundle\MobicoopBundle\I18n\Entity\Translate;

/**
 * A Source.
 *
 */
class Source
{
   
    /**
     * @var int The id of this source.
     *
    * @Groups({"get","post","put"})
     */
    private $id;
            
    /**
     * @var string The domain of the source.
     *
    * @Groups({"get","post","put"})
     */
    private $domain;

    /**
     * @var string The property of the source.
     *
    * @Groups({"get","post","put"})
     */
    private $property;

    /**
    * @var Translate[]|null A Source can have multiple entry in Translate
    * @Groups({"get","post","put"})
    */
    private $translates;

    public function __construct()
    {
        $this->translates = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }
            
    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        
        return $this;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;
        
        return $this;
    }

    public function getTranslates(): Collection
    {
        return $this->translates;
    }

    public function addTranslate(Translate $translate): self
    {
        if (!$this->translates->contains($translate)) {
            $this->translates[] = $translate;
            $translate->setSource($this);
        }

        return $this;
    }

    public function removeTranslate(Translate $translate): self
    {
        if ($this->translates->contains($translate)) {
            $this->translates->removeElement($translate);
            // set the owning side to null (unless already changed)
            if ($translate->getSource() === $this) {
                $translate->setSource(null);
            }
        }

        return $this;
    }
}
