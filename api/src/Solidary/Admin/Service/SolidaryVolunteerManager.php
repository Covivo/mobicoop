<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Admin\Service;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\SolidaryVolunteer;

/**
 * Solidary volunteer manager in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryVolunteerManager
{
    private $entityManager;
    
    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Get Solidary Volunteer records
     *
     * @param PaginatorInterface $solidaryUsers  The solidary user objects
     * @return array|null The solidary volunteer records
     */
    public function getSolidaryVolunteers(PaginatorInterface $solidaryUsers)
    {
        $solidaryVolunteers = [];
        foreach ($solidaryUsers as $solidaryUser) {
            /**
             * @var SolidaryUser $solidaryUser
             */
            $solidaryVolunteer = new SolidaryVolunteer();
            $solidaryVolunteer->setId($solidaryUser->getId());
            $solidaryVolunteer->setGivenName($solidaryUser->getUser()->getGivenName());
            $solidaryVolunteer->setFamilyName($solidaryUser->getUser()->getFamilyName());
            $solidaryVolunteer->setHomeAddress($solidaryUser->getUser()->getHomeAddress()->jsonSerialize());
            $solidaryVolunteer->setMMinTime($solidaryUser->getMMinTime());
            $solidaryVolunteer->setMMaxTime($solidaryUser->getMMaxTime());
            $solidaryVolunteer->setAMinTime($solidaryUser->getAMinTime());
            $solidaryVolunteer->setAMaxTime($solidaryUser->getAMaxTime());
            $solidaryVolunteer->setEMinTime($solidaryUser->getEMinTime());
            $solidaryVolunteer->setEMaxTime($solidaryUser->getEMaxTime());
            $solidaryVolunteer->setMMon($solidaryUser->hasMMon());
            $solidaryVolunteer->setAMon($solidaryUser->hasAMon());
            $solidaryVolunteer->setEMon($solidaryUser->hasEMon());
            $solidaryVolunteer->setMTue($solidaryUser->hasMTue());
            $solidaryVolunteer->setATue($solidaryUser->hasATue());
            $solidaryVolunteer->setETue($solidaryUser->hasETue());
            $solidaryVolunteer->setMWed($solidaryUser->hasMWed());
            $solidaryVolunteer->setAWed($solidaryUser->hasAWed());
            $solidaryVolunteer->setEWed($solidaryUser->hasEWed());
            $solidaryVolunteer->setMThu($solidaryUser->hasMThu());
            $solidaryVolunteer->setAThu($solidaryUser->hasAThu());
            $solidaryVolunteer->setEThu($solidaryUser->hasEThu());
            $solidaryVolunteer->setMFri($solidaryUser->hasMFri());
            $solidaryVolunteer->setAFri($solidaryUser->hasAFri());
            $solidaryVolunteer->setEFri($solidaryUser->hasEFri());
            $solidaryVolunteer->setMSat($solidaryUser->hasMSat());
            $solidaryVolunteer->setASat($solidaryUser->hasASat());
            $solidaryVolunteer->setESat($solidaryUser->hasESat());
            $solidaryVolunteer->setMSun($solidaryUser->hasMSun());
            $solidaryVolunteer->setASun($solidaryUser->hasASun());
            $solidaryVolunteer->setESun($solidaryUser->hasESun());
            // get the status of the volunteer for each structure attached
            $volunteerStructures = [];
            foreach ($solidaryUser->getSolidaryUserStructures() as $solidaryUserStructure) {
                /**
                 * @var SolidaryUserStructure $solidaryUserStructure
                 */

                $volunteerStructures[] = [
                    "name" => $solidaryUserStructure->getStructure()->getName(),
                    "status" => $solidaryUserStructure->getStatus()
                ];
            }
            $solidaryVolunteer->setStructures($volunteerStructures);
            $solidaryVolunteers[] = $solidaryVolunteer;
        }
        
        return $solidaryVolunteers;
    }
}
