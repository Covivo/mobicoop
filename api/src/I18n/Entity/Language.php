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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Doctrine\Common\Collections\ArrayCollection;
use App\User\Entity\User;
use App\I18n\Entity\Translate;

/**
 * A Language.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          }
 *      }
 * )
 */
class Language
{
    /**
     * @var int The id of this language.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","read"})
     */
    private $id;
            
    /**
     * @var string The code of the language.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","read","write"})
     */
    private $code;

    /**
    * @var ArrayCollection The users of the section.
    *
    * @ORM\OneToMany(targetEntity="\App\User\Entity\User", mappedBy="language")
    * @ORM\OrderBy({"position" = "ASC"})
    * @Groups({"aRead","read","write"})
    * @MaxDepth(1)
    */
    private $users;

    /**
     * @var ArrayCollection|null A Language can have multiple entry in Translate
     *
     * @ORM\OneToMany(targetEntity="\App\I18n\Entity\translate", mappedBy="language")
     * @MaxDepth(1)
     */
    private $translates;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->translates = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    /**
    * @return ArrayCollection|User[]
    */
    public function getUsers(): ArrayCollection
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

    /**
    * @return ArrayCollection|Translate[]
    */
    public function getTranslates(): ArrayCollection
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
}
