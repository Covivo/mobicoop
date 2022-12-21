<?php
/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Incentive\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Incentive\Resource\CeeStatus;
use App\Incentive\Service\CeeStatusManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class CeeStatusCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $security;
    private $ceeStatusManager;

    public function __construct(Security $security, CeeStatusManager $ceeStatusManager)
    {
        $this->security = $security;
        $this->ceeStatusManager = $ceeStatusManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CeeStatus::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        if (!$this->security->getUser() instanceof User) {
            throw new \LogicException('Only a User can make this');
        }

        return $this->ceeStatusManager->getStatus($this->security->getUser());
    }
}
