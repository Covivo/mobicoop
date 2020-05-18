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

use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Solidary\Event\SolidaryUserUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\StructureRepository;
use App\User\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Action\Repository\DiaryRepository;
use App\Solidary\Entity\SolidaryDiaryEntry;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Repository\SolidaryRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryUserManager
{
    private $entityManager;
    private $eventDispatcher;
    private $solidaryUserRepository;
    private $userRepository;
    private $security;
    private $structureRepository;
    private $diaryRepository;
    private $solidaryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        SolidaryUserRepository $solidaryUserRepository,
        UserRepository $userRepository,
        Security $security,
        StructureRepository $structureRepository,
        DiaryRepository $diaryRepository,
        SolidaryRepository $solidaryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->userRepository = $userRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->security = $security;
        $this->structureRepository = $structureRepository;
        $this->diaryRepository = $diaryRepository;
        $this->solidaryRepository = $solidaryRepository;
    }

    public function updateSolidaryUser(SolidaryUser $solidaryUser)
    {
        // We trigger the event
        $event = new SolidaryUserUpdatedEvent($solidaryUser);
        $this->eventDispatcher->dispatch(SolidaryUserUpdatedEvent::NAME, $event);
    }

    /**
     * Get a SolidaryBeneficiary from a User id
     *
     * @param int $id User id
     * @return SolidaryBeneficiary
     */
    public function getSolidaryBeneficiary(int $id): SolidaryBeneficiary
    {
        // Get the structure of the Admin
        $structures = $this->structureRepository->findByUser($this->security->getUser());
        $structureAdmin = null;
        if (!is_null($structures) || count($structures)>0) {
            $structureAdmin = $structures[0];
        }

        // Get the User
        $user = $this->userRepository->find($id);

        // Get the SolidaryUser
        if (is_null($user->getSolidaryUser())) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_USER);
        }
        $solidaryUser = $user->getSolidaryUser();

        // We check if the SolidaryUser is a Beneficiary
        if (!$solidaryUser->isBeneficiary()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_BENEFICIARY);
        }


        $solidaryBeneficiary = new SolidaryBeneficiary();
        $solidaryBeneficiary->setId($user->getId());
        $solidaryBeneficiary->setEmail($user->getEmail());
        $solidaryBeneficiary->setGivenName($user->getGivenName());
        $solidaryBeneficiary->setFamilyName($user->getFamilyName());
        $solidaryBeneficiary->setNewsSubscription($user->hasNewsSubscription());
        $solidaryBeneficiary->setTelephone($user->getTelephone());
        $solidaryBeneficiary->setBirthDate($user->getBirthDate());
        $solidaryBeneficiary->setGender($user->getGender());
        $solidaryBeneficiary->setComment($solidaryUser->getComment());
        $solidaryBeneficiary->setUser($user);

        // Home address
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $solidaryBeneficiary->setHomeAddress($address);
            }
        }

        // Proofs
        $proofs = [];

        // We take the first solidaryUser structure.
        $solidaryUserStructure = $solidaryUser->getSolidaryUserStructures()[0];
        // If the admin has an identified structure, we take the one that matches on of the SolidaryBeneficiary structure
        if (!is_null($structureAdmin)) {
            foreach ($solidaryUser->getSolidaryUserStructures() as $currentSolidaryUserStructure) {
                if ($currentSolidaryUserStructure->getId() == $structureAdmin->getId()) {
                    $solidaryUserStructure = $currentSolidaryUserStructure;
                    break;
                }
            }
        }

        /**
         * @var SolidaryUserStructure $solidaryUserStructure
         */
        foreach ($solidaryUserStructure->getProofs() as $proof) {
            $proofs[] = $proof;
        }
        $solidaryBeneficiary->setProofs($proofs);

        // Is he validated ?
        $solidaryBeneficiary->setValidatedCandidate(false);
        if (!is_null($solidaryUserStructure->getAcceptedDate())) {
            $solidaryBeneficiary->setValidatedCandidate(true);
        }

        $solidaryBeneficiary->setCreatedDate($solidaryUser->getCreatedDate());
        $solidaryBeneficiary->setUpdatedDate($solidaryUser->getUpdatedDate());

        // Get the structure of the solidary User
        $userStructures = [];
        foreach ($solidaryUser->getSolidaryUserStructures() as $userStructure) {
            $userStructures[] = $userStructure;
        }
        $solidaryBeneficiary->setStructures($userStructures);

        // Diary
        $diaries = $this->diaryRepository->findBy(['user'=>$user]);
        $diaryEntries = [];
        foreach ($diaries as $diary) {
            $diaryEntry = new SolidaryDiaryEntry();
            $diaryEntry->setDiary($diary);
            $diaryEntry->setAction($diary->getAction()->getName());
            $diaryEntry->setAuthor($diary->getAuthor());
            $diaryEntry->setUser($diary->getUser());
            $diaryEntry->setDate($diary->getCreatedDate());
            $diaryEntries[] = $diaryEntry;
        }
        $solidaryBeneficiary->setDiaries($diaryEntries);
        
        // Solidaries
        $solidaries = $this->solidaryRepository->findByUser($user);
        $solidaryBeneficiary->setSolidaries($solidaries);

        return $solidaryBeneficiary;
    }

    /**
     * Get a SolidaryVolunteer from a User id
     *
     * @param int $id User id
     * @return SolidaryVolunteer
     */
    public function getSolidaryVolunteer(int $id): SolidaryVolunteer
    {

        // Get the structure of the Admin
        $structures = $this->structureRepository->findByUser($this->security->getUser());
        $structureAdmin = null;
        if (!is_null($structures) || count($structures)>0) {
            $structureAdmin = $structures[0];
        }


        // Get the User
        $user = $this->userRepository->find($id);

        // Get the SolidaryUser
        if (is_null($user->getSolidaryUser())) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_USER);
        }
        $solidaryUser = $user->getSolidaryUser();

        // We check if the SolidaryUser is a Beneficiary
        if (!$solidaryUser->isVolunteer()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_VOLUNTEER);
        }

        $solidaryVolunteer = new SolidaryVolunteer();
        $solidaryVolunteer->setUser($user);
        $solidaryVolunteer->setComment($solidaryUser->getComment());


        // We take the first solidaryUser structure.
        $solidaryUserStructure = $solidaryUser->getSolidaryUserStructures()[0];
        // If the admin has an identified structure, we take the one that matches on of the SolidaryBeneficiary structure
        if (!is_null($structureAdmin)) {
            foreach ($solidaryUser->getSolidaryUserStructures() as $currentSolidaryUserStructure) {
                if ($currentSolidaryUserStructure->getId() == $structureAdmin->getId()) {
                    $solidaryUserStructure = $currentSolidaryUserStructure;
                    break;
                }
            }
        }

        // Is he validated ?
        $solidaryVolunteer->setValidatedCandidate(false);
        if (!is_null($solidaryUserStructure->getAcceptedDate())) {
            $solidaryVolunteer->setValidatedCandidate(true);
        }

        // Diary
        $diaries = $this->diaryRepository->findBy(['user'=>$user]);
        $diaryEntries = [];
        foreach ($diaries as $diary) {
            $diaryEntry = new SolidaryDiaryEntry();
            $diaryEntry->setDiary($diary);
            $diaryEntry->setAction($diary->getAction()->getName());
            $diaryEntry->setAuthor($diary->getAuthor());
            $diaryEntry->setUser($diary->getUser());
            $diaryEntry->setDate($diary->getCreatedDate());
            $diaryEntries[] = $diaryEntry;
        }
        $solidaryVolunteer->setDiaries($diaryEntries);

        // Solidaries
        $solidaries = $this->solidaryRepository->findBySolidaryUserMatching($solidaryUser);
        $solidaryVolunteer->setSolidaries($solidaries);


        return $solidaryVolunteer;
    }
}
