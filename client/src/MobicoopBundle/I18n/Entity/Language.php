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

namespace Mobicoop\Bundle\MobicoopBundle\I18n\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\I18n\Entity\Translate;

/**
 * A Language.
 */
class Language implements ResourceInterface, \JsonSerializable
{
    const LANGUAGES = [
        ["id"=>1,"code"=>"fr"],
        ["id"=>2,"code"=>"en"],
        ["id"=>3,"code"=>"eu"],
        ["id"=>4,"code"=>"it"],
        ["id"=>5,"code"=>"de"],
        ["id"=>6,"code"=>"es"],
        ["id"=>7,"code"=>"nl"]
    ];

    /**
     * @var int The id of this language.
     * @Groups({"get","post","put","language"})
     */
    private $id;

    /**
    * @var string|null The iri of this language.
    * @Groups({"get","post","put","language"})
    */
    private $iri;
            
    /**
     * @var string The code of the language.
     * @Groups({"get","post","put"})
     */
    private $code;

    /**
    * @var User[]|null The users of the section.
    *
    * @Groups({"get","post","put"})
    */
    private $users;

    /**
     * @var Translate[]|null A Language can have multiple entry in Translate
     *
     * @Groups({"get","post","put"})
     */
    private $translates;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/languages/".$id);
        }
        $this->users = new ArrayCollection();
        $this->translates = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }
            
    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        
        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setLanguage($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getLanguage() === $this) {
                $user->setLanguage(null);
            }
        }

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
            $translate->setLanguage($this);
        }

        return $this;
    }

    public function removeTranslate(Translate $translate): self
    {
        if ($this->translates->contains($translate)) {
            $this->translates->removeElement($translate);
            // set the owning side to null (unless already changed)
            if ($translate->getLanguage() === $this) {
                $translate->setLanguage(null);
            }
        }

        return $this;
    }
    public function jsonSerialize()
    {
        $languageSerialized = [
            'id'                    => $this->getId(),
            'code'                  => $this->getCode()
        ];
        return $languageSerialized;
    }
}
