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
use App\Solidary\Entity\Solidary;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\SolidaryManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
final class SolidaryCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $filters;
    private $solidaryManager;
    private $security;

    public function __construct(SolidaryManager $solidaryManager, Security $security)
    {
        $this->solidaryManager = $solidaryManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (isset($context['filters'])) {
            $this->filters = $context['filters'];
        }

        return Solidary::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        if ('getMySolidaries' == $operationName) {
            return $this->solidaryManager->getMySolidaries($this->security->getUser());
        }
        if (empty($this->security->getUser()->getSolidaryStructures())) {
            throw new SolidaryException(SolidaryException::NO_STRUCTURE);
        }

        $progression = null;
        $solidaryUserId = null;
        if (isset($this->filters['solidaryUser'])) {
            if (strrpos($this->filters['solidaryUser'], '/')) {
                $solidaryUserId = substr($this->filters['solidaryUser'], strrpos($this->filters['solidaryUser'], '/') + 1);
            }
            if (empty($solidaryUserId) || !is_numeric($solidaryUserId)) {
                throw new SolidaryException(SolidaryException::SOLIDARY_USER_ID_INVALID);
            }
        }
        if (isset($this->filters['progression'])) {
            $progression = $this->filters['progression'];
            if (!is_numeric($progression)) {
                throw new SolidaryException(SolidaryException::INVALID_PROGRESSION);
            }
        }

        return $this->solidaryManager->getSolidaries($this->security->getUser()->getSolidaryStructures()[0], $solidaryUserId, $progression);
    }
}
