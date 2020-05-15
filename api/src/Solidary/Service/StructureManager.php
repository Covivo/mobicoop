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

namespace App\Solidary\Service;

use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StructureManager
{
    private $entityManager;
    private $structureRepository;
    private $structureProofRepository;

    public function __construct(EntityManagerInterface $entityManager, StructureProofRepository $structureProofRepository, StructureRepository $structureRepository)
    {
        $this->entityManager = $entityManager;
        $this->structureRepository = $structureRepository;
        $this->structureProofRepository = $structureProofRepository;
    }

    public function getStructureProofs(int $structureId)
    {

        // We get the structure
        $structure = $this->structureRepository->find($structureId);

        // If there is a structureId, we use it
        return $this->structureProofRepository->findStructureProofs($structure);
    }
}
