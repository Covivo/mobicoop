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

namespace App\User\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Review made by a User on another User
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readReview"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeReview"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('review_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('review_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('review_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "put"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "delete"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Review
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int $id The id of this Review.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readReview","readPublicProfile"})
     */
    private $id;

    /**
     * @var User The User who left this Review
     *
     * @Assert\NotBlank
     * @Groups({"readReview","writeReview","readPublicProfile"})
     */
    private $reviewer;

    /**
     * @var User The User targeted by this Review
     *
     * @Assert\NotBlank
     * @Groups({"readReview","writeReview"})
     */
    private $reviewed;

    /**
     * @var string The content text of the review
     *
     * @Assert\NotBlank
     * @Groups({"readReview","writeReview","readPublicProfile"})
     */
    private $content;

    /**
     * @var bool True if the review has already been left, Left if it's waiting
     *
     * @Groups({"readReview"})
     */
    private $left;

    /**
     * @var \DateTimeInterface Creation date.
     * @Groups({"readReview","readPublicProfile"})
     */
    private $date;

    public function __construct(int $id = null)
    {
        $this->id = self::DEFAULT_ID;
        if (!is_null($id)) {
            $this->id = $id;
        }
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

    public function getReviewer(): ?User
    {
        return $this->reviewer;
    }

    public function setReviewer(?User $reviewer): self
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    public function getReviewed(): ?User
    {
        return $this->reviewed;
    }

    public function setReviewed(?User $reviewed): self
    {
        $this->reviewed = $reviewed;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isLeft(): ?bool
    {
        return $this->left;
    }

    public function setLeft(?bool $left): self
    {
        $this->left = $left;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
