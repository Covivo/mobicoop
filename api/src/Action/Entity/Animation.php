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

namespace App\Action\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidarySolution;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An animation action
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readAnimation"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeAnimation"}}
 *      },
 *      collectionOperations={
 *          "post"
 *      }
 * )
 */
class Animation
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this animation action.
     * @ApiProperty(identifier=true)
     * @Groups("readAnimation")
     */
    private $id;

    /**
     * @var string Name of the animation action.
     * @Assert\NotBlank
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $name;

    /**
     * @var string Comment of the animation action
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $comment;

    /**
     * @var User The user related with the animation action.
     * @Assert\NotBlank
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $user;

    /**
     * @var User The author of the animation action.
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $author;

    /**
     * @var int|null The progression if the action can be related to a process (like for solidary records). It's a numeric value, so it can be a percent, a step...
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $progression;

    /**
     * @var Solidary|null The solidary record if the action concerns a solidary record.
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $solidary;

    /**
     * @var SolidarySolution|null The solidary solution if the action concerns a solidary record solution.
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $solidarySolution;


    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        
        return $this;
    }

    public function getProgression(): ?string
    {
        return $this->progression;
    }

    public function setProgression(?string $progression): self
    {
        $this->progression = $progression;

        return $this;
    }

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }
    
    public function setSolidary(?Solidary $solidary): self
    {
        $this->solidary = $solidary;
        
        return $this;
    }

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }
    
    public function setSolidarySolution(?SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;
        
        return $this;
    }
}
