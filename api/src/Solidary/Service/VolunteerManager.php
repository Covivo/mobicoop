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
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\StructureProof;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;

class VolunteerManager
{
    private $entityManager;
    private $userManager;
    private $structureRepository;
    private $structureProofRepository;

    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, StructureRepository $structureRepository, StructureProofRepository $structureProofRepository)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->structureRepository = $structureRepository;
        $this->structureProofRepository = $structureProofRepository;
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
        if (empty($preparedUser)) {
            $user = new User();
            $user->setEmail($exposedVolunteer->getEmail());
            $user->setGivenName($exposedVolunteer->getGivenName());
            $user->setFamilyName($exposedVolunteer->getFamilyName());
            $user->setGender($exposedVolunteer->getGender());
            $user->setBirthDate($exposedVolunteer->getBirthDate());
            $user->setPassword($exposedVolunteer->getPassword());
            $user->setPhoneDisplay($exposedVolunteer->getPhoneDisplay());
            $preparedUser = $this->userManager->prepareUser($user, true);
        }
        // We set the userId of the exposed volunteer, because we return it
        $exposedVolunteer->setUserId($preparedUser->getId());

        // Next, we need to create a true Volonteer
        $volunteer = new Volunteer();
        // The prepared User
        $volunteer->setUser($preparedUser);
        // The classic params of a volunteer
        $volunteer->setAddress($exposedVolunteer->getAddress());
        $volunteer->setMaxDistance($exposedVolunteer->getMaxDistance());
        (!is_null($exposedVolunteer->hasVehicle())) ? $volunteer->setVehicle($exposedVolunteer->hasVehicle()) : $volunteer->setVehicle(false);

        //  Find the structure and set it
        $structure = $this->structureRepository->find($exposedVolunteer->getStructure());
        if (!empty($structure)) {
            $volunteer->setStructure($structure);
        }

        
        $volunteer->setComment($exposedVolunteer->getComment());
        
        // Needs
        foreach ($exposedVolunteer->getNeeds() as $currentNeed) {
            // TO DO : Handle the needs
            // $need = new Need();
            // $volunteer->addNeed($need);
        }
        // Proofs
        foreach ($exposedVolunteer->getProofs() as $currentProof) {
            $proof = new Proof();
            $proof->setValue($currentProof['value']);
            $structureProof = $this->structureProofRepository->find($currentProof['structureProof']);
            if (!empty($structureProof)) {
                $proof->setStructureProof($structureProof);
            }
            $volunteer->addProof($proof);
        }

        $this->entityManager->persist($volunteer);
        $this->entityManager->flush();

        return $exposedVolunteer;
    }
}
