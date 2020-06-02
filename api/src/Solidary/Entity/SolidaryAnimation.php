<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Entity;

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
 *          "get"={
 *              "security"="is_granted('solidary_animation_list',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_animation_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)"
 *          },
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAnimation
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this SolidaryAnimation action.
     * @ApiProperty(identifier=true)
     * @Groups("readAnimation")
     */
    private $id;

    /**
     * @var string Name of the action executed by this SolidaryAnimation
     * @Assert\NotBlank
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $actionName;

    /**
     * @var string Comment of the SolidaryAnimation action
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $comment;

    /**
     * @var User The user related with the SolidaryAnimation action.
     * @Assert\NotBlank
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $user;

    /**
     * @var User The author of the SolidaryAnimation action.
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $author;

    /**
     * @var int|null The progression if the SolidaryAnimation can be related to a process (like for solidary records). It's a numeric value, so it can be a percent, a step...
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $progression;

    /**
     * @var Solidary|null The solidary record if the SolidaryAnimation concerns a solidary record.
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $solidary;

    /**
     * @var SolidarySolution|null The solidary solution if the SolidaryAnimation concerns a solidary record solution.
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $solidarySolution;

    /**
     * @var \DateTimeInterface Creation date of the diary action.
     * @Groups({"readAnimation"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the diary action.
     * @Groups({"readAnimation"})
     */
    private $updatedDate;

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

    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    public function setActionName(string $actionName): self
    {
        $this->actionName = $actionName;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
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

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }
}
