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
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\SolidaryManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidarySearchCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $filters;
    private $solidaryManager;

    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (isset($context['filters'])) {
            $this->filters = $context['filters'];
        }

        return SolidarySearch::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?SolidarySearch
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

        if (empty($this->filters['type']) || !in_array($this->filters['type'], ['carpool', 'transport'])) {
            throw new SolidaryException(SolidaryException::TYPE_MISSING_OR_INVALID);
        }
        if (empty($this->filters['way']) || !in_array($this->filters['way'], ['outward', 'return'])) {
            throw new SolidaryException(SolidaryException::WAY_MISSING_OR_INVALID);
        }

        // Creating the SolidarySearch entity for the service
        $solidarySearch = new SolidarySearch();
        $solidarySearch->setWay($this->filters['way']);

        $solidary = $this->solidaryManager->getSolidary($solidaryId);

        $solidarySearch->setSolidary($solidary);
        if ('transport' == $this->filters['type']) {
            return $this->solidaryManager->getSolidaryTransportSearchResults($solidarySearch);
        }
        if ('carpool' == $this->filters['type']) {
            return $this->solidaryManager->getSolidaryCarpoolSearchSearchResults($solidarySearch);
        }

        return null;
    }
}
