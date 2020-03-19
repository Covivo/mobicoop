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

use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\StructureProof;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Exception\SolidaryException;

class SolidaryUserManager
{
    private $entityManager;
    private $userManager;
    private $structureRepository;
    private $structureProofRepository;
    private $solidaryUserRepository;

    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, StructureRepository $structureRepository, StructureProofRepository $structureProofRepository, SolidaryUserRepository $solidaryUserRepository)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->structureRepository = $structureRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
    }
    
    // /**
    //  * Create a Volunteer from an ExposedVolunteer with the User account if necessary
    //  *
    //  * @param ExposedVolunteer $exposedVolunteer
    //  * @return ExposedVolunteer|null
    //  */
    // public function createVolunteer(ExposedVolunteer $exposedVolunteer)
    // {
    //     // First, we check if this volunteer already exists
    //     $volunteer = $this->getVolunteerByEmail($exposedVolunteer->getEmail());
    //     if (!is_null($volunteer)) {
    //         throw new SolidaryException(SolidaryException::VOLUNTEER_ALREADY_EXISTS);
    //     }
        
    //     // We need to create a User behind this volonteer (if it doesn't exist)

    //     $preparedUser = $this->userManager->getUserByEmail($exposedVolunteer->getEmail());
    //     if (empty($preparedUser)) {
    //         $user = new User();
    //         $user->setEmail($exposedVolunteer->getEmail());
    //         $user->setGivenName($exposedVolunteer->getGivenName());
    //         $user->setFamilyName($exposedVolunteer->getFamilyName());
    //         $user->setGender($exposedVolunteer->getGender());
    //         $user->setBirthDate($exposedVolunteer->getBirthDate());
    //         $user->setPassword($exposedVolunteer->getPassword());
    //         $user->setPhoneDisplay($exposedVolunteer->getPhoneDisplay());
    //         $preparedUser = $this->userManager->prepareUser($user, true);
    //     }
    //     // We set the userId of the exposed volunteer, because we return it
    //     $exposedVolunteer->setUserId($preparedUser->getId());


    //     $volunteer = $this->buildVolunteer($preparedUser, $exposedVolunteer);

    //     $this->entityManager->persist($volunteer);
    //     $this->entityManager->flush();

    //     return $exposedVolunteer;
    // }

    // /**
    //  * Build a Volunter from an exposed Volunteer
    //  *
    //  * @param User $user
    //  * @param ExposedVolunteer $exposedVolunteer
    //  * @return void
    //  */
    // public function buildVolunteer(User $user, ExposedVolunteer $exposedVolunteer)
    // {
    //     // We look for a pre existing volunteer (in case of an update for exemple)
    //     // If it does'nt exists (in case of a creation) we instanciate a new one
    //     $volunteer = $this->volunteerRepository->find($exposedVolunteer->getId());
    //     if (is_null($volunteer)) {
    //         $volunteer = new Volunteer();
    //     }
        
    //     $volunteer->setUser($user);

    //     // The classic params of a volunteer
    //     $volunteer->setAddress($exposedVolunteer->getAddress());
    //     $volunteer->setMaxDistance($exposedVolunteer->getMaxDistance());
    //     (!is_null($exposedVolunteer->hasVehicle())) ? $volunteer->setVehicle($exposedVolunteer->hasVehicle()) : $volunteer->setVehicle(false);

    //     //  Find the structure and set it
    //     $structure = $this->structureRepository->find($exposedVolunteer->getStructure());
    //     if (!empty($structure)) {
    //         $volunteer->setStructure($structure);
    //     }

        
    //     $volunteer->setComment($exposedVolunteer->getComment());
        
    //     // Needs
    //     foreach ($exposedVolunteer->getNeeds() as $currentNeed) {
    //         // TO DO : Handle the needs
    //         // $need = new Need();
    //         // $volunteer->addNeed($need);
    //     }
    //     // Proofs
    //     foreach ($exposedVolunteer->getProofs() as $currentProof) {
    //         $proof = new Proof();
    //         $proof->setValue((is_array($currentProof)) ? $currentProof['value'] : $currentProof->getValue());
    //         $structureProof = $this->structureProofRepository->find((is_array($currentProof)) ? $currentProof['structureProof'] : $currentProof->getStructureProof()->getId());
    //         if (!empty($structureProof)) {
    //             $proof->setStructureProof($structureProof);
    //         }
    //         $volunteer->addProof($proof);
    //     }

    //     // Availabilities - Times
    //     if (!is_null($exposedVolunteer->getMMinTime())) {
    //         $volunteer->setMMinTime($exposedVolunteer->getMMinTime());
    //     }
    //     if (!is_null($exposedVolunteer->getMMaxTime())) {
    //         $volunteer->setMMaxTime($exposedVolunteer->getMMaxTime());
    //     }
    //     if (!is_null($exposedVolunteer->getAMinTime())) {
    //         $volunteer->setAMinTime($exposedVolunteer->getAMinTime());
    //     }
    //     if (!is_null($exposedVolunteer->getAMaxTime())) {
    //         $volunteer->setAMaxTime($exposedVolunteer->getAMaxTime());
    //     }
    //     if (!is_null($exposedVolunteer->getEMinTime())) {
    //         $volunteer->setEMinTime($exposedVolunteer->getEMinTime());
    //     }
    //     if (!is_null($exposedVolunteer->getEMaxTime())) {
    //         $volunteer->setEMaxTime($exposedVolunteer->getEMaxTime());
    //     }

    //     // Availabilities - Days
    //     if (!is_null($exposedVolunteer->hasMMon())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMMon());
    //     }
    //     if (!is_null($exposedVolunteer->hasAMon())) {
    //         $volunteer->setAMon($exposedVolunteer->hasAMon());
    //     }
    //     if (!is_null($exposedVolunteer->hasEMon())) {
    //         $volunteer->setEMon($exposedVolunteer->hasEMon());
    //     }
    //     if (!is_null($exposedVolunteer->hasMTue())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMTue());
    //     }
    //     if (!is_null($exposedVolunteer->hasATue())) {
    //         $volunteer->setAMon($exposedVolunteer->hasATue());
    //     }
    //     if (!is_null($exposedVolunteer->hasETue())) {
    //         $volunteer->setEMon($exposedVolunteer->hasETue());
    //     }
    //     if (!is_null($exposedVolunteer->hasMWed())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMWed());
    //     }
    //     if (!is_null($exposedVolunteer->hasAWed())) {
    //         $volunteer->setAMon($exposedVolunteer->hasAWed());
    //     }
    //     if (!is_null($exposedVolunteer->hasEWed())) {
    //         $volunteer->setEMon($exposedVolunteer->hasEWed());
    //     }
    //     if (!is_null($exposedVolunteer->hasMThu())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMThu());
    //     }
    //     if (!is_null($exposedVolunteer->hasAThu())) {
    //         $volunteer->setAMon($exposedVolunteer->hasAThu());
    //     }
    //     if (!is_null($exposedVolunteer->hasEThu())) {
    //         $volunteer->setEMon($exposedVolunteer->hasEThu());
    //     }
    //     if (!is_null($exposedVolunteer->hasMFri())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMFri());
    //     }
    //     if (!is_null($exposedVolunteer->hasAFri())) {
    //         $volunteer->setAMon($exposedVolunteer->hasAFri());
    //     }
    //     if (!is_null($exposedVolunteer->hasEFri())) {
    //         $volunteer->setEMon($exposedVolunteer->hasEFri());
    //     }
    //     if (!is_null($exposedVolunteer->hasMSat())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMSat());
    //     }
    //     if (!is_null($exposedVolunteer->hasASat())) {
    //         $volunteer->setAMon($exposedVolunteer->hasASat());
    //     }
    //     if (!is_null($exposedVolunteer->hasESat())) {
    //         $volunteer->setEMon($exposedVolunteer->hasESat());
    //     }
    //     if (!is_null($exposedVolunteer->hasMSun())) {
    //         $volunteer->setMMon($exposedVolunteer->hasMSun());
    //     }
    //     if (!is_null($exposedVolunteer->hasASun())) {
    //         $volunteer->setAMon($exposedVolunteer->hasASun());
    //     }
    //     if (!is_null($exposedVolunteer->hasESun())) {
    //         $volunteer->setEMon($exposedVolunteer->hasESun());
    //     }

    //     return $volunteer;
    // }
    // /**
    //  * Get a Volunteer (exposed)
    //  *
    //  * @param integer $id   Id of the volunteer
    //  * @return ExposedVolunteer|null
    //  */
    // public function getVolunteer(int $id)
    // {
    //     $volunteer = $this->volunteerRepository->find($id);
        
    //     if (is_null($volunteer)) {
    //         return null;
    //     }
        
    //     return $this->buildExposedVolunteer($volunteer);
    // }

    // /**
    //  * Get a Volunteer by its email
    //  *
    //  * @param string $email
    //  * @return ExposedVolunteer|null
    //  */
    // public function getVolunteerByEmail(string $email)
    // {
    //     $volunteer = $this->volunteerRepository->findByEmail($email);
    //     if (is_array($volunteer) && count($volunteer)>0) {
    //         return $this->buildExposedVolunteer($volunteer[0]);
    //     }
    //     return null;
    // }

    // /**
    //  * Build an exposed Volunteer from a Volunteer
    //  *
    //  * @param Volunteer $volunteer
    //  * @return ExposedVolunteer
    //  */
    // public function buildExposedVolunteer(Volunteer $volunteer)
    // {
    //     $exposedVolunteer = new ExposedVolunteer();
    //     $exposedVolunteer->setId($volunteer->getId());
    //     $exposedVolunteer->setEmail($volunteer->getUser()->getEmail());
    //     $exposedVolunteer->setGivenName($volunteer->getUser()->getGivenName());
    //     $exposedVolunteer->setFamilyName($volunteer->getUser()->getFamilyName());
    //     $exposedVolunteer->setGender($volunteer->getUser()->getGender());
    //     $exposedVolunteer->setBirthDate($volunteer->getUser()->getBirthDate());
    //     $exposedVolunteer->setPassword($volunteer->getUser()->getPassword());
    //     $exposedVolunteer->setPhoneDisplay($volunteer->getUser()->getPhoneDisplay());
    //     $exposedVolunteer->setMaxDistance($volunteer->getMaxDistance());
    //     $exposedVolunteer->setVehicle($volunteer->hasVehicle());
    //     $exposedVolunteer->setComment($volunteer->getComment());
    //     $exposedVolunteer->setNeeds($volunteer->getNeeds());
    //     $exposedVolunteer->setProofs($volunteer->getProofs());
    //     $exposedVolunteer->setAddress($volunteer->getAddress());
    //     $exposedVolunteer->setStructure($volunteer->getStructure()->getId());

    //     // Availabilities - Times
    //     if (!is_null($volunteer->getMMinTime())) {
    //         $exposedVolunteer->setMMinTime($volunteer->getMMinTime());
    //     }
    //     if (!is_null($volunteer->getMMaxTime())) {
    //         $exposedVolunteer->setMMaxTime($volunteer->getMMaxTime());
    //     }
    //     if (!is_null($volunteer->getAMinTime())) {
    //         $exposedVolunteer->setAMinTime($volunteer->getAMinTime());
    //     }
    //     if (!is_null($volunteer->getAMaxTime())) {
    //         $exposedVolunteer->setAMaxTime($volunteer->getAMaxTime());
    //     }
    //     if (!is_null($volunteer->getEMinTime())) {
    //         $exposedVolunteer->setEMinTime($volunteer->getEMinTime());
    //     }
    //     if (!is_null($volunteer->getEMaxTime())) {
    //         $exposedVolunteer->setEMaxTime($volunteer->getEMaxTime());
    //     }

    //     // Availabilities - Days
    //     if (!is_null($volunteer->hasMMon())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMMon());
    //     }
    //     if (!is_null($volunteer->hasAMon())) {
    //         $exposedVolunteer->setAMon($volunteer->hasAMon());
    //     }
    //     if (!is_null($volunteer->hasEMon())) {
    //         $exposedVolunteer->setEMon($volunteer->hasEMon());
    //     }
    //     if (!is_null($volunteer->hasMTue())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMTue());
    //     }
    //     if (!is_null($volunteer->hasATue())) {
    //         $exposedVolunteer->setAMon($volunteer->hasATue());
    //     }
    //     if (!is_null($volunteer->hasETue())) {
    //         $exposedVolunteer->setEMon($volunteer->hasETue());
    //     }
    //     if (!is_null($volunteer->hasMWed())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMWed());
    //     }
    //     if (!is_null($volunteer->hasAWed())) {
    //         $exposedVolunteer->setAMon($volunteer->hasAWed());
    //     }
    //     if (!is_null($volunteer->hasEWed())) {
    //         $exposedVolunteer->setEMon($volunteer->hasEWed());
    //     }
    //     if (!is_null($volunteer->hasMThu())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMThu());
    //     }
    //     if (!is_null($volunteer->hasAThu())) {
    //         $exposedVolunteer->setAMon($volunteer->hasAThu());
    //     }
    //     if (!is_null($volunteer->hasEThu())) {
    //         $exposedVolunteer->setEMon($volunteer->hasEThu());
    //     }
    //     if (!is_null($volunteer->hasMFri())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMFri());
    //     }
    //     if (!is_null($volunteer->hasAFri())) {
    //         $exposedVolunteer->setAMon($volunteer->hasAFri());
    //     }
    //     if (!is_null($volunteer->hasEFri())) {
    //         $exposedVolunteer->setEMon($volunteer->hasEFri());
    //     }
    //     if (!is_null($volunteer->hasMSat())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMSat());
    //     }
    //     if (!is_null($volunteer->hasASat())) {
    //         $exposedVolunteer->setAMon($volunteer->hasASat());
    //     }
    //     if (!is_null($volunteer->hasESat())) {
    //         $exposedVolunteer->setEMon($volunteer->hasESat());
    //     }
    //     if (!is_null($volunteer->hasMSun())) {
    //         $exposedVolunteer->setMMon($volunteer->hasMSun());
    //     }
    //     if (!is_null($volunteer->hasASun())) {
    //         $exposedVolunteer->setAMon($volunteer->hasASun());
    //     }
    //     if (!is_null($volunteer->hasESun())) {
    //         $exposedVolunteer->setEMon($volunteer->hasESun());
    //     }


    //     return $exposedVolunteer;
    // }


    // /**
    //  * Update a volunteer
    //  *
    //  * @param int $id                               Id of the true volunteer
    //  * @param ExposedVolunteer $exposedVolunteer    The exposed volunter with data to update
    //  * @return ExposedVolunteer
    //  */
    // public function updateVolunteer(int $id, ExposedVolunteer $exposedVolunteer)
    // {
    //     // Get the original Volunteer
    //     $volunteer = $this->volunteerRepository->find($id);

    //     //$volunteer = $this->buildVolunteer($volunteer->getUser(), $exposedVolunteer);

    //     //$volunteer->setMaxDistance(1);

    //     // TO DO : Circual reference error !



    //     $this->entityManager->persist($volunteer);
    //     //$this->entityManager->flush();
        
    //     return $exposedVolunteer;
    // }
}
