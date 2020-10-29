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

namespace App\User\Service;

use App\User\Ressource\Review;
use App\User\Entity\Review as ReviewEntity;
use App\User\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Review manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ReviewManager
{
    private $entityManager;
    private $reviewRepository;

    public function __construct(EntityManagerInterface $entityManager, ReviewRepository $reviewRepository)
    {
        $this->entityManager = $entityManager;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Build a Review (Ressource) from en Review (Entity)
     *
     * @param ReviewEntity $reviewEntity    The review Entity
     * @return Review
     */
    public function buildReviewFromEntity(ReviewEntity $reviewEntity): Review
    {
        $review = new Review($reviewEntity->getId());

        $review->setReviewer($reviewEntity->getReviewer());
        $review->setReviewed($reviewEntity->getReviewed());
        $review->setContent($reviewEntity->getContent());
        $review->setCreatedDate($reviewEntity->getCreatedDate());
        $review->getUpdatedDate($reviewEntity->getUpdatedDate());

        return $review;
    }

    /**
     * Build a Review (Entity) from en Review (Ressource)
     *
     * @param Review $review    The review Entity
     * @return ReviewEntity
     */
    public function buildReviewFromRessource(Review $review): ReviewEntity
    {
        $reviewEntity = new ReviewEntity();

        $reviewEntity->setReviewer($review->getReviewer());
        $reviewEntity->setReviewed($review->getReviewed());
        $reviewEntity->setContent($review->getContent());

        return $reviewEntity;
    }

    /**
     * Create a Review
     *
     * @param Review $review
     * @return Review
     */
    public function createReview(Review $review): Review
    {
        $reviewEntity = $this->buildReviewFromRessource($review);
        $this->entityManager->persist($reviewEntity);
        $this->entityManager->flush();

        return $this->buildReviewFromEntity($reviewEntity);
    }

    /**
     * Get a Review
     *
     * @param integer $id
     * @return Review|null
     */
    public function getReview(int $id): ?Review
    {
        $reviewEntity = $this->reviewRepository->find($id);
        if (!is_null($reviewEntity)) {
            return $this->buildReviewFromEntity($reviewEntity);
        }
        return null;
    }
}
