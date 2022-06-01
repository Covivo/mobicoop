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

namespace App\Event\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Event\Entity\Event;
use App\Event\Service\EventManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Event search by userId.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
final class EventIsCreatedByUserCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $eventManager;

    public function __construct(RequestStack $requestStack, eventManager $eventManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->eventManager = $eventManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return event::class === $resourceClass && $operationName === "created";
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return $this->eventManager->getCreatedEvents($this->request->get("userId"));
    }
}
