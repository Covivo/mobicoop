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
 */

namespace App\Solidary\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An animation action.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readAnimation"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeAnimation"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('solidary_animation_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_animation_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAnimation
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of this SolidaryAnimation action
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
     * @var User the user related with the SolidaryAnimation action
     * @Assert\NotBlank
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $user;

    /**
     * @var User the author of the SolidaryAnimation action
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $author;

    /**
     * @var null|int The progression if the SolidaryAnimation can be related to a process (like for solidary records). It's a numeric value, so it can be a percent, a step...
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $progression;

    /**
     * @var null|Solidary the solidary record if the SolidaryAnimation concerns a solidary record
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $solidary;

    /**
     * @var null|SolidarySolution the solidary solution if the SolidaryAnimation concerns a solidary record solution
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $solidarySolution;

    /**
     * @var \DateTimeInterface creation date of the diary action
     * @Groups({"readAnimation"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the diary action
     * @Groups({"readAnimation"})
     */
    private $updatedDate;

    /**
     * @var null|User the transporter associated to the SolidaryAnimation action
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $transporter;

    /**
     * @var null|User the carpooler associated to the SolidaryAnimation action
     * @Groups({"readAnimation","writeAnimation"})
     */
    private $carpooler;

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

    public function getTransporter(): ?User
    {
        return $this->transporter;
    }

    public function setTransporter(?User $transporter): self
    {
        $this->transporter = $transporter;

        return $this;
    }

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(?User $carpooler): self
    {
        $this->carpooler = $carpooler;

        return $this;
    }
}
