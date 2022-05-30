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
use App\Solidary\Entity\SolidaryVolunteerPlanning\SolidaryVolunteerPlanning;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\SolidaryManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidaryVolunteerPlanningCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $solidaryManager;
    private $context;

    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;
        return SolidaryVolunteerPlanning::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        if (!isset($this->context['filters']['startDate'])) {
            $startDate = new \DateTime();
        } else {
            $startDate = new \DateTime($this->context['filters']['startDate']);
        }

        if (!isset($this->context['filters']['endDate'])) {
            $endDate = clone $startDate;
            $endDate->modify('+2 week');
        } else {
            $endDate = new \DateTime($this->context['filters']['endDate']);
        }

        if (!isset($this->context['filters']['solidaryVolunteerId'])) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_VOLUNTEER_ID);
        }
        $solidaryVolunteerId = -1;
        if (strrpos($this->context['filters']['solidaryVolunteerId'], '/')) {
            $solidaryVolunteerId = substr($this->context['filters']['solidaryVolunteerId'], strrpos($this->context['filters']['solidaryVolunteerId'], '/') + 1);
        } elseif (is_numeric($this->context['filters']['solidaryVolunteerId'])) {
            $solidaryVolunteerId = $this->context['filters']['solidaryVolunteerId'];
        }
        if ($solidaryVolunteerId==-1 || !is_numeric($solidaryVolunteerId)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_VOLUNTEER_ID_INVALID);
        }

        return $this->solidaryManager->buildSolidaryVolunteerPlanning($startDate, $endDate, $solidaryVolunteerId);
    }
}
