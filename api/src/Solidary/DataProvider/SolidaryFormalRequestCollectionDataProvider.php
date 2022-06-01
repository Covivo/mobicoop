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
use App\Solidary\Entity\SolidaryFormalRequest;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\SolidarySolutionManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidaryFormalRequestCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $filters;
    private $security;
    private $solidarySolutionManager;

    public function __construct(Security $security, SolidarySolutionManager $solidarySolutionManager)
    {
        $this->security = $security;
        $this->solidarySolutionManager = $solidarySolutionManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (isset($context['filters'])) {
            $this->filters = $context['filters'];
        }

        return SolidaryFormalRequest::class === $resourceClass && $operationName = 'get';
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        if (empty($this->filters['solidary_solutions'])) {
            throw new SolidaryException(SolidaryException::SOLIDARY_SOLUTION_MISSING);
        }

        $solidarySolutionId = null;
        if (strrpos($this->filters['solidary_solutions'], '/')) {
            $solidarySolutionId = substr($this->filters['solidary_solutions'], strrpos($this->filters['solidary_solutions'], '/') + 1);
        }
        if (empty($solidarySolutionId) || !is_numeric($solidarySolutionId)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_SOLUTION_ID_INVALID);
        }

        return $this->solidarySolutionManager->getSolidaryFormalRequest($solidarySolutionId);
    }
}
