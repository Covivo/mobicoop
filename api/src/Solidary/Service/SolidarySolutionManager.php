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
        // The SolidaryUser has to be a volunteer
        if (!is_null($solidarySolution->getSolidaryUser()) && !$solidarySolution->getSolidaryUser()->isVolunteer()) {
            throw new SolidaryException(SolidaryException::IS_NOT_VOLUNTEER);
        }
        // Can't have both matching et solidaryUser
        if (!is_null($solidarySolution->getSolidaryUser()) && !is_null($solidarySolution->getMatching())) {
            throw new SolidaryException(SolidaryException::CANT_HAVE_BOTH);
        }

        $this->entityManager->persist($solidarySolution);
        $this->entityManager->flush();
        return $solidarySolution;
    }
}
