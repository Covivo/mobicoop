<?php

/*
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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
// constructeur prend en paramètre un implémentation de Checher.php

namespace App\Monitor\Core\Application\Service;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Monitor\Core\Application\Port\Checker;
use App\Monitor\Interfaces\CheckNotificationAbuse;
use Symfony\Component\HttpFoundation\JsonResponse;

final class NotificationAbuseChecker implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $_notificationAbuseChecker;

    public function __construct(Checker $notificationAbuseChecker)
    {
        $this->_notificationAbuseChecker = $notificationAbuseChecker;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return CheckNotificationAbuse::class === $resourceClass && 'get' == $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null): JsonResponse
    {
        return new JsonResponse($this->_notificationAbuseChecker->check(), 200, [], true);
    }
}
