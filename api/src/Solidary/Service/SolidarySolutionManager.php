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

use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryMatchingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class SolidarySolutionManager
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Create a SolidarySolution
     *
     * @param SolidarySolution $solidarySolution
     * @return SolidarySolution|null
     */
    public function createSolidarySolution(SolidarySolution $solidarySolution): ?SolidarySolution
    {
        // If there is a SolidaryUser, it has to be a volunteer
        if (!is_null($solidarySolution->getSolidaryMatching()->getSolidaryUser()) && !$solidarySolution->getSolidaryMatching()->getSolidaryUser()->isVolunteer()) {
            throw new SolidaryException(SolidaryException::IS_NOT_VOLUNTEER);
        }
        // Can't have both matching et solidaryUser
        if (!is_null($solidarySolution->getSolidaryMatching()->getSolidaryUser()) && !is_null($solidarySolution->getSolidaryMatching()->getMatching())) {
            throw new SolidaryException(SolidaryException::CANT_HAVE_BOTH);
        }

        // We get the Solidary of this SolidaryMatching and set it for the SolidarySolution ((yeah, it a shortcut for the model)
        $solidarySolution->setSolidary($solidarySolution->getSolidaryMatching()->getSolidary());
        
        $this->entityManager->persist($solidarySolution);
        $this->entityManager->flush();
        return $solidarySolution;
    }

    /**
     * Make a formal request for a SolidarySolution
     *
     * @param SolidarySolution $solidarySolution
     * @return SolidarySolution|null
     */
    public function makeFormalRequest(SolidarySolution $solidarySolution) : ?SolidarySolution
    {
        /*****  Update the criteria of the SolidaryAsk */

        // Get the solidaryAsk
        $solidaryAsk = $solidarySolution->getSolidaryAsk();
        if (is_null($solidaryAsk)) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_ASK);
        }


        /*****  If this is a Carpool Solidary Solution, we need to update the carpool Ask and its Criteria */


        /*****  Update the status of the SolidaryAsk */

        return $solidarySolution;
    }
}
