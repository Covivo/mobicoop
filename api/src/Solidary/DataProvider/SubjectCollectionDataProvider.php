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
use App\Solidary\Entity\Subject;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\StructureManager;
use Symfony\Component\Security\Core\Security;
use App\User\Entity\User;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SubjectCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $filters;
    private $structureManager;
    private $security;

    public function __construct(StructureManager $structureManager, Security $security)
    {
        $this->structureManager = $structureManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (isset($context['filters'])) {
            $this->filters = $context['filters'];
        }
        return Subject::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        $structureId = null;

        // If there is a structureId we use it
        if (!empty($this->filters['structureId'])) {
            $structureId = $this->filters['structureId'];
        }

        // If the user whose making the request has a structure, we use its id
        if (!empty($this->security->getUser()->getSolidaryStructures())) {
            $structureId = $this->security->getUser()->getSolidaryStructures()[0]->getId();
        }

        if (is_null($structureId)) {
            // We found no structureId we can't process this method
            throw new SolidaryException(SolidaryException::NO_STRUCTURE_ID);
        }

        return $this->structureManager->getStructureSubjects($structureId);
    }
}
