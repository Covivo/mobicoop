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

use App\Solidary\Entity\Volunteer;
use App\Solidary\Entity\Exposed\Volunteer as ExposedVolunteer;
use App\User\Entity\User;
use App\User\Service\UserManager;

class VolunteerManager
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }
    
    /**
     * Create a Volunteer from an ExposedVolunteer with the User account if necessary
     *
     * @param ExposedVolunteer $exposedVolunteer
     * @return ExposedVolunteer|null
     */
    public function createVolunteer(ExposedVolunteer $exposedVolunteer)
    {
        // First, we need to create a User behind this volonteer (if it doesn't exist)

        $preparedUser = $this->userManager->getUserByEmail($exposedVolunteer->getEmail());
        if (empty($user)) {
            $user = new User();
            $user->setEmail($exposedVolunteer->getEmail());
            $user->setGivenName($exposedVolunteer->getGivenName());
            $user->setFamilyName($exposedVolunteer->getFamilyName());
            $user->setGender($exposedVolunteer->getGender());
            $user->setBirthDate($exposedVolunteer->getBirthDate());
            $user->setPassword($exposedVolunteer->getPassword());
            $user->setPhoneDisplay($exposedVolunteer->getPhoneDisplay());
            $preparedUser = $this->userManager->prepareUser($user, true);
            // We set the userId of the exposed volunteer, because we return it
            $exposedVolunteer->setUserId($preparedUser->getId());
        }

        // Next, we need to create a true Volonteer
        $volunteer = new Volunteer();
        // The prepared User
        $volunteer->setUser($preparedUser);
        // The classic params of a volunteer
        $volunteer->setAddress($exposedVolunteer->getAddress());
        $volunteer->setMaxDistance($exposedVolunteer->getMaxDistance());
        (!is_null($exposedVolunteer->hasVehicle())) ? $volunteer->setVehicle($exposedVolunteer->hasVehicle()) : $volunteer->setVehicle(false);
        $volunteer->setStructure($exposedVolunteer->getStructure());
        $volunteer->setComment($exposedVolunteer->getComment());
        
        // Needs
        foreach ($exposedVolunteer->getNeeds() as $currentNeed) {
            $volunteer->addNeed($need);
        }
        // Proofs
        foreach ($exposedVolunteer->getProofs() as $currentProof) {
            $proof = new Proof();
            $volunteer->addProof($proof);
        }


        return $exposedVolunteer;
    }
}
