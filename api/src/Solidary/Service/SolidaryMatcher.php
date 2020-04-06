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

use App\Carpool\Entity\Proposal;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Repository\SolidaryMatchingRepository;
use Doctrine\ORM\EntityManagerInterface;

class SolidaryMatcher
{
    private $solidaryMatchingRepository;
    private $entityManager;

    public function __construct(SolidaryMatchingRepository $solidaryMatchingRepository, EntityManagerInterface $entityManager)
    {
        $this->solidaryMatchingRepository = $solidaryMatchingRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Build and persist the solidary matchings for a Solidary based on a set of solidary search results
     *
     * @param Solidary $solidary    The solidary
     * @param array $results        The results of a solidary search
     * @return array|null
     */
    public function buildSolidaryMatchings(Solidary $solidary, array $results): ?array
    {
        $solidaryMatchings = [];
        
        // We get the previous SolidaryMatchings of this solidary
        $previousMatchings = $this->solidaryMatchingRepository->findSolidaryMatchingOfSolidary($solidary);

        foreach ($results as $solidaryUser) {

            // We check if the matching already exists
            $matchingAlreadyExists = false;
            foreach ($previousMatchings as $previousMatching) {
                if ($previousMatching->getSolidaryUser()->getId() == $solidaryUser->getId() &&
                $previousMatching->getSolidary()->getId() == $solidary->getId()
                ) {
                    $matchingAlreadyExists = true;
                    // We keep the previous matching
                    $solidaryMatching = $previousMatching;
                    break;
                }
            }

            // If this matching doesn't already exists we persist it and we add it to the return
            if (!$matchingAlreadyExists) {
                $solidaryMatching = new SolidaryMatching();
                $solidaryMatching->setSolidaryUser($solidaryUser);
                $solidaryMatching->setSolidary($solidary);
                $this->entityManager->persist($solidaryMatching);
                $this->entityManager->flush();
                // We add the matching the return list
                $solidaryMatchings[] = $solidaryMatching;
            } else {
                // We check if there already is a SolidaryAsk for this matching
                $solidaryAsk = $this->solidaryMatchingRepository->findAskOfSolidaryMatching($solidaryMatching);

                // There is no Ask, we can add this solidaryMatching to the return
                if (is_null($solidaryAsk)) {
                    $solidaryMatchings[] = $solidaryMatching;
                }
            }
        }

        return $solidaryMatchings;
    }
}
