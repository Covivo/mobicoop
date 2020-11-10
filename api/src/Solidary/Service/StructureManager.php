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

use App\Geography\Repository\TerritoryRepository;
use App\Solidary\Entity\Structure;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Repository\SubjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
     * Get the StructureProofs of a Structure
     *
     * @param integer $structureId
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
     * Get the Subjects of a Structure
     *
     * @param integer $structureId
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
     *
     * @param float $lat    Latitude
     * @param float $lon    Longitude
     * @return array|null   Array of Structures
     */
    public function getGeolocalisedStructures(float $lat, float $lon): ?array
    {
        // This array will contain the valid structures
        $validStructures = [];

        // First, we retreive the territories containing $lat/$lon point
        $geolocTerritories = $this->territoryRepository->findPointTerritories($lat, $lon);
        // foreach($geolocTerritories as $geolocTerritory){
        // Usefull to show the found territories
        //     echo $geolocTerritory->getId()." ".$geolocTerritory->getName()."\n";

        // }

        // Get all structures
        $structures = $this->structureRepository->findAll();

        // Check if the structure is in at least one appropriate territory
        foreach ($structures as $structure) {
            $structureValid = false;
            foreach ($structure->getTerritories() as $structureTerritory) {
                foreach ($geolocTerritories as $geolocTerritory) {
                    if ($geolocTerritory->getId() == $structureTerritory->getId()) {
                        $structureValid = true;
                        break;
                    }
                }
                if ($structureValid) {
                    break;
                }
            }
            if ($structureValid) {
                $validStructures[] = $structure;
            }
        }

        return $validStructures;
    }
}
