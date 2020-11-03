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

use App\Carpool\Repository\AskRepository;
use App\User\Ressource\Review;
use App\User\Entity\Review as ReviewEntity;
use App\User\Entity\User;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\User\Repository\ReviewRepository;
use App\User\Ressource\ReviewsDashboard;
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
    private $askRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ReviewRepository $reviewRepository,
        AskRepository $askRepository
    ) {
        $this->entityManager = $entityManager;
        $this->reviewRepository = $reviewRepository;
        $this->askRepository = $askRepository;
    }

    /**
     * Build a Review (Ressource) from en Review (Entity)
     *
     * @param ReviewEntity $reviewEntity    The review Entity
     * @param boolean $isLeft               true : the review has already been left
     * @return Review
     */
    private function buildReviewFromEntity(ReviewEntity $reviewEntity, bool $isLeft = true): Review
    {
        $review = new Review($reviewEntity->getId());
        
        $review->setReviewer($reviewEntity->getReviewer());
        $review->setReviewed($reviewEntity->getReviewed());

        $review->setContent(nl2br($reviewEntity->getContent()));

        $review->setDate((!is_null($reviewEntity->getUpdatedDate())) ? $reviewEntity->getUpdatedDate() : $reviewEntity->getCreatedDate());
        $review->setLeft($isLeft);

        return $review;
    }

    
    /**
     * Build an array of Review (Ressources) from an array of Review (Entities)
     *
     * @param ReviewEntity[] $reviewEntities    The array of Review entities
     * @param boolean $isLeft                   true : the review has already been left
     * @return Review[]
     */
    private function buildReviewsFromEntities(array $reviewEntities, bool $isLeft = true): array
    {
        $reviews = [];
        foreach ($reviewEntities as $reviewEntity) {
            $reviews[] = $this->buildReviewFromEntity($reviewEntity, $isLeft);
        }
        return $reviews;
    }

    /**
     * Build a Review (Entity) from en Review (Ressource)
     *
     * @param Review $review    The review Entity
     * @return ReviewEntity
     */
    private function buildReviewFromRessource(Review $review): ReviewEntity
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

    
    /**
     * Get reviews with specific reviewer and/or specific reviewed
     *
     * @param User $reviewer The reviewer
     * @param User $reviewed The reviewed
     * @return Review[]
     */
    public function getSpecificReviews(User $reviewer=null, User $reviewed=null)
    {
        $reviewEntities = $this->reviewRepository->findSpecificReviews($reviewer, $reviewed);
        if (!is_null($reviewEntities)) {
            return $this->buildReviewsFromEntities($reviewEntities);
        }
        return [];
    }
    
    
    /**
     * Return all reviews concerning a User
     * Those who he's the reviewer, the reviewed or those he can still leave on someone.
     *
     * @param User $reviewer The reviewer
     * @param User $reviewed A specifiec reviewed
     * @return Review[]
     */
    public function getReviews(User $reviewer, User $reviewed=null): array
    {
        $reviews = [];

        // We get the reviews involving the User as reviewer or reviewd
        $reviewEntities = $this->reviewRepository->findReviewsInvolvingUser($reviewer);
        foreach ($reviewEntities as $reviewEntity) {
            $reviews[] = $this->buildReviewFromEntity($reviewEntity);
        }

        // We get the list of the Users already reviewed
        $userLeftReviews = $this->getSpecificReviews($reviewer, $reviewed);
        $userIdAlreadyReviewed = [];
        foreach ($userLeftReviews as $userLeftReview) {
            if (!in_array($userLeftReview->getReviewed()->getId(), $userIdAlreadyReviewed)) {
                $userIdAlreadyReviewed[] = $userLeftReview->getReviewed()->getId();
            }
        }
        
        // We get the accepted Ask involving the User
        $asks = $this->askRepository->findAcceptedAsksForUser($reviewer);
        foreach ($asks as $ask) {
            // We keep only oneway or outward
            if ($ask->getType() == Ask::TYPE_ONE_WAY || $ask->getType() == Ask::TYPE_OUTWARD_ROUNDTRIP) {

                // We will check if the review is already available to be left
                $reviewAvailable = false;
                
                // Determine the reviewed
                if ($ask->getUser()->getId()==$reviewer->getId()) {
                    $reviewed = $ask->getUserRelated();
                } else {
                    $reviewed = $ask->getUser();
                }
                

                // We check if the User has already reviewed the other user
                if (in_array($reviewed->getId(), $userIdAlreadyReviewed)) {
                    // Already reviewed this user. We break the loop
                    break;
                }

                $now = new \DateTime();
                $nowDate = \DateTime::createFromFormat("d/m/Y H:i:s", $now->format("d/m/Y")." 00:00:00");
                // $nowDate = \DateTime::createFromFormat("d/m/Y H:i:s", "03/11/2020 00:00:00");
                $fromDate = clone $ask->getCriteria()->getFromDate();

                if ($ask->getCriteria()->getFrequency()==Criteria::FREQUENCY_PUNCTUAL) {
                    // If the ask is punctual, the carpool must be passed
                    if ($nowDate>$fromDate) {
                        $reviewAvailable = true;
                    }
                } else {
                    // The Ask is regular, the first week has to be passed
                    $day = $fromDate->format('w');
                    $diffToTheEndOfFirstWeek = ($day==0) ? 0 : 7 - $day;
                    
                    $endOfFirstWeekDate = $fromDate->modify('+'.$diffToTheEndOfFirstWeek.'day');
                    if ($nowDate>$endOfFirstWeekDate) {
                        $reviewAvailable = true;
                    }
                }

                if ($reviewAvailable) {
                    // We create a Review to leave from the Ask
                    $reviewToLeave = new Review();
                    $reviewToLeave->setReviewer($reviewer);
                    $reviewToLeave->setReviewed($reviewed);
                    $reviewToLeave->setLeft(false);
                    $reviews[] = $reviewToLeave;
                }
            }
        }
        return $reviews;
    }

    /**
     * Return the reviews Dashboard of a User
     *
     * @param User $user
     * @return ReviewsDashboard
     */
    public function getReviewDashboard(User $reviewer, User $reviewed=null): ReviewsDashboard
    {
        $reviewDashboard = new ReviewsDashboard();
        
        // Get all reviews involving the User
        $reviews = $this->getReviews($reviewer, $reviewed);
        foreach ($reviews as $review) {
            if (!$review->isLeft()) {
                // It's a review to give
                $reviewDashboard->addReviewsToGive($review);
            } else {
                if ($review->getReviewer()->getId() == $reviewer->getId()) {
                    $reviewDashboard->addGivenReviews($review);
                } elseif ($review->getReviewed()->getId() == $reviewer->getId()) {
                    $reviewDashboard->addReceivedReviews($review);
                }
            }
        }

        return $reviewDashboard;
    }
}
