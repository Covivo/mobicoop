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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Review Dashboard
 */
class ReviewsDashboard implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int $id The id of this Review.
     */
    private $id;
   
    /**
     * @var bool True if the review system is enabled
     */
    private $reviewActive;
    
    /**
     * @var Reviews[] Given reviews
     */
    private $givenReviews;

    /**
     * @var Reviews[] Received reviews
     */
    private $receivedReviews;

    /**
     * @var Reviews[] Reviews to give
     */
    private $reviewsToGive;

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

    public function jsonSerialize()
    {
        return [
            'id'                        => $this->getId(),
            'reviewActive'              => $this->isReviewActive(),
            'givenReviews'              => $this->getGivenReviews(),
            'receivedReviews'           => $this->getReceivedReviews(),
            'reviewsToGive'             => $this->getReviewsToGive(),
        ];
    }
}
