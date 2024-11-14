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

namespace Mobicoop\Bundle\MobicoopBundle\User\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Review;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\ReviewDashboard;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Review management service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ReviewManager
{
    private $dataProvider;
    private $carpoolTimezone;

    /**
     * Constructor.
     */
    public function __construct(DataProvider $dataProvider, string $carpoolTimezone)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Review::class);
        $this->carpoolTimezone = $carpoolTimezone;
    }

    /**
     * Create a review left by the reviewer about the reviewed.
     */
    public function createReview(int $reviewerId, int $reviewedId, string $content): bool
    {
        $review = new Review();
        $review->setReviewer(new User($reviewerId));
        $review->setReviewed(new User($reviewedId));
        $review->setContent($content);

        $response = $this->dataProvider->post($review);
        if (201 == $response->getCode()) {
            return true;
        }

        return false;
    }

    /**
     * Get the Review Dashboard of a User.
     */
    public function reviewDashboard(): ?array
    {
        $this->dataProvider->setClass(ReviewDashboard::class);
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getCollection();
        if (200 == $response->getCode()) {
            return $response->getValue();
        }

        return null;
    }
}
