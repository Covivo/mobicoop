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

namespace App\Solidary\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Service\SolidaryUserManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidaryBeneficiaryCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $solidaryUserManager;
    private $context;

    public function __construct(SolidaryUserManager $solidaryUserManager)
    {
        $this->solidaryUserManager = $solidaryUserManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;
        return SolidaryBeneficiary::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        // We check and sanitize the filters
        $filters = null;
        if (isset($this->context['filters'])) {
            $filters = [];
            foreach ($this->context['filters'] as $key => $value) {
                if (in_array($key, SolidaryBeneficiary::AUTHORIZED_FILTERS)) {
                    $filters[$key] = $value;
                }
            }
        }

        return $this->solidaryUserManager->getSolidaryBeneficiaries($filters);
    }
}
