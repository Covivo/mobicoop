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

namespace App\User\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Ressource\Review;
use App\User\Service\ReviewManager;

/**
 * Profile Summary item dataprovider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class ReviewItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $reviewManager;

    public function __construct(ReviewManager $reviewManager)
    {
        $this->reviewManager = $reviewManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Review::class === $resourceClass && 'get' == $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Review
    {
        $review = $this->reviewManager->getReview($id);
        if (is_null($review)) {
            throw new \LogicException('Review not found found');
        }

        return $review;
    }
}
