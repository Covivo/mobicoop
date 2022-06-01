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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Ressource\Review;

/**
 * A Review Dashboard with Given reviews, Received reviews and reviews to give
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","readReview"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeReview"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('review_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ReviewDashboard
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this Review.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"read","readReview"})
     */
    private $id;

    /**
     * @var bool True if the review system is enabled
     * @Groups({"read","readReview"})
     */
    private $reviewActive;

    /**
     * @var Review[] Given reviews
     *
     * @Groups({"readReview"})
     */
    private $givenReviews;

    /**
     * @var Review[] Received reviews
     *
     * @Groups({"readReview"})
     */
    private $receivedReviews;

    /**
     * @var Review[] Reviews to give
     *
     * @Groups({"readReview"})
     */
    private $reviewsToGive;

    public function __construct(int $id = null)
    {
        $this->id = self::DEFAULT_ID;
        if (!is_null($id)) {
            $this->id = $id;
        }

        $this->givenReviews = [];
        $this->receivedReviews = [];
        $this->reviewsToGive = [];
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

    public function isReviewActive(): bool
    {
        return $this->reviewActive;
    }

    public function setReviewActive(bool $reviewActive): self
    {
        $this->reviewActive = $reviewActive;

        return $this;
    }

    public function getGivenReviews(): ?array
    {
        return $this->givenReviews;
    }

    public function setGivenReviews(?array $givenReviews): self
    {
        $this->givenReviews = $givenReviews;

        return $this;
    }

    public function addGivenReviews(Review $givenReview): self
    {
        $this->givenReviews[] = $givenReview;

        return $this;
    }

    public function getReceivedReviews(): ?array
    {
        return $this->receivedReviews;
    }

    public function setReceivedReviews(?array $receivedReviews): self
    {
        $this->receivedReviews = $receivedReviews;

        return $this;
    }

    public function addReceivedReviews(Review $receivedReview): self
    {
        $this->receivedReviews[] = $receivedReview;

        return $this;
    }

    public function getReviewsToGive(): ?array
    {
        return $this->reviewsToGive;
    }

    public function setReviewsToGive(?array $reviewsToGive): self
    {
        $this->reviewsToGive = $reviewsToGive;

        return $this;
    }

    public function addReviewsToGive(Review $reviewToGive): self
    {
        $this->reviewsToGive[] = $reviewToGive;

        return $this;
    }
}
