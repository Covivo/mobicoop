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
use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Service\SolidaryUserManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
final class SolidaryBeneficiaryCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $solidaryUserManager;
    private $context;
    private $security;

    public function __construct(SolidaryUserManager $solidaryUserManager, Security $security)
    {
        $this->solidaryUserManager = $solidaryUserManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;

        return SolidaryBeneficiary::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        // We check and sanitize the filters
        $filters = null;
        $validatedCandidate = null;
        if (isset($this->context['filters'])) {
            $filters = [];
            foreach ($this->context['filters'] as $key => $value) {
                if (in_array($key, SolidaryBeneficiary::AUTHORIZED_GENERIC_FILTERS)) {
                    $filters[$key] = $value;
                } elseif (SolidaryBeneficiary::VALIDATED_CANDIDATE_FILTER == $key) {
                    $validatedCandidate = ('true' == $value) ? $validatedCandidate = true : $validatedCandidate = false;
                }
            }
        }

        return $this->solidaryUserManager->getSolidaryBeneficiaries($filters, $validatedCandidate);
    }
}
