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

namespace App\Solidary\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Solidary\Entity\SolidaryAnimation;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\SolidaryAnimationManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidaryAnimationCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $solidaryAnimationManager;
    private $filters;

    public function __construct(SolidaryAnimationManager $solidaryAnimationManager)
    {
        $this->solidaryAnimationManager = $solidaryAnimationManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (isset($context['filters'])) {
            $this->filters = $context['filters'];
        }

        return SolidaryAnimation::class === $resourceClass && 'get' == $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        if (empty($this->filters['solidary'])) {
            throw new SolidaryException(SolidaryException::SOLIDARY_MISSING);
        }

        $solidaryId = null;
        if (strrpos($this->filters['solidary'], '/')) {
            $solidaryId = substr($this->filters['solidary'], strrpos($this->filters['solidary'], '/') + 1);
        }
        if (empty($solidaryId) || !is_numeric($solidaryId)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_ID_INVALID);
        }

        return $this->solidaryAnimationManager->getSolidaryAnimations($solidaryId);
    }
}
