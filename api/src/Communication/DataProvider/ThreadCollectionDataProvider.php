<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Communication\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Complet message thread collection DataProvider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class ThreadCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $security;
    private $internalMessageManager;

    public function __construct(RequestStack $requestStack, Security $security, InternalMessageManager $internalMessageManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->internalMessageManager = $internalMessageManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Message::class === $resourceClass && 'completeThread' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return $this->internalMessageManager->getCompleteThread($this->request->get('idMessage'), true, $this->security->getUser()->getId());
    }
}
