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

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Ressource\SsoConnection;
use App\User\Service\SsoManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SsoConnectionCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $security;
    private $ssoManager;
    private $request;

    public function __construct(RequestStack $request, Security $security, SsoManager $ssoManager)
    {
        $this->security = $security;
        $this->ssoManager = $ssoManager;
        $this->request = $request->getCurrentRequest();
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return SsoConnection::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null)
    {
        if ('' == $this->request->get('baseSiteUri')) {
            throw new \LogicException('Parameter missing : baseSiteUri');
        }

        return $this->ssoManager->getSsoConnectionServices($this->request->get('baseSiteUri'), $this->request->get('serviceId'), $this->request->get('redirectUri'), $this->request->get('origin'));
    }
}
