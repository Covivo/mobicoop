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
use App\Carpool\Service\CarpoolExportManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data provider for user's carpoolExports.
 */
final class UserCarpoolExportItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public const FROM_DATE = 'fromDate';
    public const TO_DATE = 'toDate';
    private $security;
    private $carpoolExportManager;

    public function __construct(Security $security, CarpoolExportManager $carpoolExportManager)
    {
        $this->security = $security;
        $this->carpoolExportManager = $carpoolExportManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && 'getCarpoolExport' === $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): User
    {
        $fromDate = $context['filters'][self::FROM_DATE];
        $toDate = $context['filters'][self::TO_DATE];

        /**
         * @var User
         */
        $user = $this->security->getUser();
        $user->setCarpoolExport($this->carpoolExportManager->getCarpoolExports($fromDate, $toDate));

        return $user;
    }
}
