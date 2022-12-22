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

namespace App\Solidary\Service;

use App\Geography\Repository\TerritoryRepository;
use App\Solidary\Entity\Structure;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Repository\SubjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StructureManager
{
    private $entityManager;
    private $structureRepository;
    private $structureProofRepository;
    private $subjectRepository;
    private $territoryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        StructureProofRepository $structureProofRepository,
        StructureRepository $structureRepository,
        SubjectRepository $subjectRepository,
        TerritoryRepository $territoryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->structureRepository = $structureRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->subjectRepository = $subjectRepository;
        $this->territoryRepository = $territoryRepository;
    }

    /**
     * Get a Structure.
     *
     * @return Structure
     */
    public function getStructure(int $structureId): ?Structure
    {
        return $this->structureRepository->find($structureId);
    }

    /**
     * Get the StructureProofs of a Structure.
     *
     * @return array
     */
    public function getStructureProofs(int $structureId): ?array
    {
        // We get the structure
        $structure = $this->structureRepository->find($structureId);

        // If there is a structureId, we use it
        return $this->structureProofRepository->findStructureProofs($structure);
    }

    /**
     * Get the Subjects of a Structure.
     *
     * @return array
     */
    public function getStructureSubjects(int $structureId): ?array
    {
        // We get the structure
        $structure = $this->structureRepository->find($structureId);

        // If there is a structureId, we use it
        return $this->subjectRepository->findStructureSubjects($structure);
    }

    /**
     * Get the list of a Structure near a lat/lon location
     * Only returns territories containing the lat/lon defined point.
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     *
     * @return null|array Array of Structures
     */
    public function getGeolocalisedStructures(float $lat, float $lon): ?array
    {
        $structures = $this->structureRepository->findByPoint($lon, $lat);

        $territoriesId = $this->territoryRepository->findPointTerritoriesIds($lat, $lon);
        foreach ($structures as $structure) {
            foreach ($structure->getTerritories() as $territory) {
                if (!in_array($territory->getId(), $territoriesId)) {
                    $structure->removeTerritory($territory);
                }
            }
        }

        return $structures;
    }
}
