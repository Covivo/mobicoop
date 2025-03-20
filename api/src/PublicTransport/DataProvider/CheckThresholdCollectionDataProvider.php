<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\PublicTransport\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Action\Repository\LogRepository;
use App\Geography\Repository\TerritoryRepository;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Service\ProviderFinder;
use App\PublicTransport\Service\PTDataProvider;
use App\PublicTransport\Service\ThresholdComputer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class CheckThresholdCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $_request;
    private $_territoryRepository;
    private $_logRepository;
    private $_ptProviders;

    public function __construct(RequestStack $requestStack, TerritoryRepository $territoryRepository, LogRepository $logRepository, array $ptProviders)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_territoryRepository = $territoryRepository;
        $this->_ptProviders = $ptProviders;
        $this->_logRepository = $logRepository;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return PTJourney::class === $resourceClass && 'checkThreshold' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null): JsonResponse
    {
        if (
            is_null($this->_request->get('origin_latitude'))
            && is_null($this->_request->get('origin_longitude'))
        ) {
            return null;
        }

        $providerFinder = new ProviderFinder($this->_territoryRepository, $this->_ptProviders, $this->_request->get('origin_latitude'), $this->_request->get('origin_longitude'));
        $provider = $providerFinder->findProvider();

        $threshold = isset($this->_ptProviders[$providerFinder->getTerritoryId()]['threshold']) && (int) $this->_ptProviders[$providerFinder->getTerritoryId()]['threshold'] > 0 ? (int) $this->_ptProviders[$providerFinder->getTerritoryId()]['threshold'] : PTDataProvider::DEFAULT_THRESHOLD;
        $threshold_granularity = isset($this->_ptProviders[$providerFinder->getTerritoryId()]['threshold_granularity']) && in_array($this->_ptProviders[$providerFinder->getTerritoryId()]['threshold_granularity'], PTDataProvider::DEFAULT_AUTHORIZED_THRESHOLD_GRANULARITY) ? $this->_ptProviders[$providerFinder->getTerritoryId()]['threshold_granularity'] : PTDataProvider::DEFAULT_THRESHOLD_GRANULARITY;
        $thresholdComputer = new ThresholdComputer($this->_logRepository, $provider['dataprovider'], $threshold, $threshold_granularity);

        $response = '{"thresholdReached":'.json_encode($thresholdComputer->isReached()).'}';

        return new JsonResponse($response, 200, [], true);
    }
}
