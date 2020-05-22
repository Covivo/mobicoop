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
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Entity\Solidary;
use App\Solidary\Service\SolidaryManager;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
final class SolidaryCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $solidaryManager;

    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Solidary::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $structureId = null;
        // If the user whose making the request has a structure, we use its id
        if (!empty($this->security->getUser()->getSolidaryStructures())) {
            $structureId = $this->security->getUser()->getSolidaryStructures()[0]->getId();
        }
 
        if (is_null($structureId)) {
            // We found no structureId we can't process this method
            throw new SolidaryException(SolidaryException::NO_STRUCTURE_ID);
        }
        if ($operationName=="getMySolidaries") {
            return $this->solidaryManager->getMySolidaries($this->security->getUser());
        }
       
        return $this->solidaryManager->getSolidaries($$this->security->getUser()->getSolidaryStructures()[0]->getStructure());
    }
}
