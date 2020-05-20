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
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Geography\Entity\Address;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\SolidaryDiaryEntry;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Entity\Structure;
use App\Solidary\Event\SolidaryCreatedEvent;
use App\Solidary\Event\SolidaryUserCreatedEvent;
use App\Solidary\Event\SolidaryUserStructureAcceptedEvent;
use App\Solidary\Event\SolidaryUserStructureRefusedEvent;
use App\Solidary\Repository\SolidaryRepository;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Solidary\Repository\StructureProofRepository;
use App\User\Service\UserManager;

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
    private $authItemRepository;
    private $structureProofRepository;
    private $params;
    private $encoder;
    private $userManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        SolidaryUserRepository $solidaryUserRepository,
        UserRepository $userRepository,
        Security $security,
        StructureRepository $structureRepository,
        DiaryRepository $diaryRepository,
        SolidaryRepository $solidaryRepository,
        AuthItemRepository $authItemRepository,
        UserPasswordEncoderInterface $encoder,
        StructureProofRepository $structureProofRepository,
        UserManager $userManager,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->userRepository = $userRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->security = $security;
        $this->structureRepository = $structureRepository;
        $this->diaryRepository = $diaryRepository;
        $this->solidaryRepository = $solidaryRepository;
        $this->authItemRepository = $authItemRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->params = $params;
        $this->encoder = $encoder;
        $this->userManager = $userManager;
    }

    // Probably obsolete... to do check !
    public function updateSolidaryUser(SolidaryUser $solidaryUser)
    {
        // We trigger the event
        $event = new SolidaryUserUpdatedEvent($solidaryUser);
        $this->eventDispatcher->dispatch(SolidaryUserUpdatedEvent::NAME, $event);
    }

    /**
     * Get a SolidaryBeneficiary from a User id
     *
     * @param int $id SolidaryUser id
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

        // Get the Solidary User
        $solidaryUser = $this->solidaryUserRepository->find($id);
        $user = $solidaryUser->getUser();

        // If user is null, we try to get the user via the repository. It appends after a post of SolidaryBeneficiary during the return. Why ? I don't know, feel free to check ;)
        if (is_null($user)) {
            $user = $this->userRepository->findOneBy(["solidaryUser"=>$solidaryUser]);
        }

        // Get the SolidaryUser
        if (is_null($user->getSolidaryUser())) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_USER);
        }

        // We check if the SolidaryUser is a Beneficiary
        if (!$solidaryUser->isBeneficiary()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_BENEFICIARY);
        }


        $solidaryBeneficiary = new SolidaryBeneficiary();
        $solidaryBeneficiary->setId($solidaryUser->getId());
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
                $homeAddress = [];
                $homeAddress['streetAddress'] = $address->getStreetAddress();
                $homeAddress['addressLocality'] = $address->getAddressLocality();
                $homeAddress['localAdmin'] = $address->getLocalAdmin();
                $homeAddress['county'] = $address->getCounty();
                $homeAddress['macroCounty'] = $address->getMacroCounty();
                $homeAddress['region'] = $address->getRegion();
                $homeAddress['macroRegion'] = $address->getMacroRegion();
                $homeAddress['addressCountry'] = $address->getAddressCountry();
                $homeAddress['countryCode'] = $address->getCountryCode();
                $homeAddress['latitude'] = $address->getLatitude();
                $homeAddress['longitude'] = $address->getLongitude();
                $solidaryBeneficiary->setHomeAddress($homeAddress);
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
        $solidaryBeneficiary->setValidatedCandidate(null);
        if (!is_null($solidaryUserStructure->getRefusedDate())) {
            $solidaryBeneficiary->setValidatedCandidate(false);
        } elseif (!is_null($solidaryUserStructure->getAcceptedDate())) {
            $solidaryBeneficiary->setValidatedCandidate(true);
        }

        $solidaryBeneficiary->setCreatedDate($solidaryUser->getCreatedDate());
        $solidaryBeneficiary->setUpdatedDate($solidaryUser->getUpdatedDate());

        // Get the structure of the solidary User
        $userStructures = [];
        foreach ($solidaryUser->getSolidaryUserStructures() as $userStructure) {
            $userStructures[] = $userStructure->getStructure();
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
     * @param int $id SolidaryVolunteer id
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

        // Get the Solidary User
        $solidaryUser = $this->solidaryUserRepository->find($id);
        $user = $solidaryUser->getUser();

        // If user is null, we try to get the user via the repository. It appends after a post of SolidaryBeneficiary during the return. Why ? I don't know, feel free to check ;)
        if (is_null($user)) {
            $user = $this->userRepository->findOneBy(["solidaryUser"=>$solidaryUser]);
        }

        // Get the SolidaryUser
        if (is_null($user->getSolidaryUser())) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_USER);
        }

        // We check if the SolidaryUser is a Beneficiary
        if (!$solidaryUser->isVolunteer()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_VOLUNTEER);
        }

        $solidaryVolunteer = new SolidaryVolunteer();
        $solidaryVolunteer->setId($solidaryUser->getId());
        $solidaryVolunteer->setUser($user);
        $solidaryVolunteer->setEmail($user->getEmail());
        $solidaryVolunteer->setGivenName($user->getGivenName());
        $solidaryVolunteer->setFamilyName($user->getFamilyName());
        $solidaryVolunteer->setNewsSubscription($user->hasNewsSubscription());
        $solidaryVolunteer->setTelephone($user->getTelephone());
        $solidaryVolunteer->setBirthDate($user->getBirthDate());
        $solidaryVolunteer->setGender($user->getGender());
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

        // Availabilities
        $solidaryVolunteer->setMMinTime($solidaryUser->getMMinTime());
        $solidaryVolunteer->setMMaxTime($solidaryUser->getMMaxTime());
        $solidaryVolunteer->setAMinTime($solidaryUser->getAMinTime());
        $solidaryVolunteer->setAMaxTime($solidaryUser->getAMaxTime());
        $solidaryVolunteer->setEMinTime($solidaryUser->getEMinTime());
        $solidaryVolunteer->setEMaxTime($solidaryUser->getEMaxTime());
        
        $solidaryVolunteer->setMMon($solidaryUser->hasMMon());
        $solidaryVolunteer->setMTue($solidaryUser->hasMTue());
        $solidaryVolunteer->setMWed($solidaryUser->hasMWed());
        $solidaryVolunteer->setMThu($solidaryUser->hasMThu());
        $solidaryVolunteer->setMFri($solidaryUser->hasMFri());
        $solidaryVolunteer->setMSat($solidaryUser->hasMSat());
        $solidaryVolunteer->setMSun($solidaryUser->hasMSun());
        $solidaryVolunteer->setAMon($solidaryUser->hasAMon());
        $solidaryVolunteer->setATue($solidaryUser->hasATue());
        $solidaryVolunteer->setAWed($solidaryUser->hasAWed());
        $solidaryVolunteer->setAThu($solidaryUser->hasAThu());
        $solidaryVolunteer->setAFri($solidaryUser->hasAFri());
        $solidaryVolunteer->setASat($solidaryUser->hasASat());
        $solidaryVolunteer->setASun($solidaryUser->hasASun());
        $solidaryVolunteer->setEMon($solidaryUser->hasEMon());
        $solidaryVolunteer->setETue($solidaryUser->hasETue());
        $solidaryVolunteer->setEWed($solidaryUser->hasEWed());
        $solidaryVolunteer->setEThu($solidaryUser->hasEThu());
        $solidaryVolunteer->setEFri($solidaryUser->hasEFri());
        $solidaryVolunteer->setESat($solidaryUser->hasESat());
        $solidaryVolunteer->setESun($solidaryUser->hasESun());

        // Dates
        $solidaryVolunteer->setCreatedDate($solidaryUser->getCreatedDate());
        $solidaryVolunteer->setUpdatedDate($solidaryUser->getUpdatedDate());

        return $solidaryVolunteer;
    }

    /**
     * Get all the SolidaryBeneficiaries
     * @var array $filters The filters
     * @return array
     */
    public function getSolidaryBeneficiaries(array $filters=null): array
    {
        $beneficiaries = [];

        $candidatValide = null;
        if (!is_null($filters) && isset($filters['candidatValide'])) {
            $candidatValide = $filters['candidatValide'];
        }

        // First, we get all user with Beneficiary types of SolidaryUser
        $users = $this->userRepository->findUsersBySolidaryUserType(SolidaryBeneficiary::TYPE, $candidatValide);
        foreach ($users as $user) {
            // Maybe To do : If it's too slow, we can use the User instead of the Id. But we need to rewrite the ItemDataProvider
            $beneficiaries[] = $this->getSolidaryBeneficiary($user->getSolidaryUser()->getId());
        }


        return $beneficiaries;
    }

    /**
     * Get all the SolidaryVolunteers
     *
     * @return array
     */
    public function getSolidaryVolunteers(): array
    {
        $volunteers = [];

        // First, we get all user with Beneficiary types of SolidaryUser
        $users = $this->userRepository->findUsersBySolidaryUserType(SolidaryVolunteer::TYPE);
        foreach ($users as $user) {
            // Maybe To do : If it's too slow, we can use the User instead of the Id. But we need to rewrite the ItemDataProvider
            $volunteers[] = $this->getSolidaryVolunteer($user->getSolidaryUser()->getId());
        }


        return $volunteers;
    }

    /**
     * Create a SolidaryUser and its User if necessary from a SolidaryBeneficiary
     *
     * @param SolidaryBeneficiary $solidaryBeneficiary
     * @return SolidaryBeneficiary|null
     */
    public function createSolidaryBeneficiary(SolidaryBeneficiary $solidaryBeneficiary): ?SolidaryBeneficiary
    {

        // If there is no User, we need to create it first
        $user = $solidaryBeneficiary->getUser();
        if (is_null($user)) {

            // We check if there no User with this email
            if (!is_null($this->userRepository->findOneBy(['email'=>$solidaryBeneficiary->getEmail()]))) {
                throw new SolidaryException(SolidaryException::ALREADY_USER);
            }


            $user = new User();
            $user->setEmail($solidaryBeneficiary->getEmail());
            $user->setGivenName($solidaryBeneficiary->getGivenName());
            $user->setFamilyName($solidaryBeneficiary->getFamilyName());
            $user->setNewsSubscription($solidaryBeneficiary->hasNewsSubscription());
            $user->setTelephone($solidaryBeneficiary->getTelephone());
            $user->setBirthDate($solidaryBeneficiary->getBirthDate());
            $user->setGender($solidaryBeneficiary->getGender());

            $user->setPhoneDisplay(1);
            $user->setSmoke($this->params['smoke']);
            $user->setMusic($this->params['music']);
            $user->setChat($this->params['chat']);
            // To do : Dynamic Language
            $user->setLanguage('fr_FR');

            // Set an encrypted password
            $password = $this->randomPassword();
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $user->setClearPassword($password); // Used to be send by email (not persisted)

            // auto valid the registration
            $user->setValidatedDate(new \DateTime());
        } else {
            // We check if this User does'nt already have a Solidary User
            if (!is_null($user->getSolidaryUser())) {
                throw new SolidaryException(SolidaryException::ALREADY_SOLIDARY_USER);
            }
        }
        
        $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE);
        $userAuthAssignment = new UserAuthAssignment();
        $userAuthAssignment->setAuthItem($authItem);
        $user->addUserAuthAssignment($userAuthAssignment);

        // We create the SolidaryUser
        $solidaryUser = new SolidaryUser();
        $solidaryUser->setBeneficiary(true);
        $homeAddress = $solidaryBeneficiary->getHomeAddress();
        $address = new Address();
        $address->setStreetAddress($homeAddress['streetAddress']);
        $address->setAddressLocality($homeAddress['addressLocality']);
        $address->setLocalAdmin($homeAddress['localAdmin']);
        $address->setCounty($homeAddress['county']);
        $address->setMacroCounty($homeAddress['macroCounty']);
        $address->setRegion($homeAddress['region']);
        $address->setMacroRegion($homeAddress['macroRegion']);
        $address->setAddressCountry($homeAddress['addressCountry']);
        $address->setCountryCode($homeAddress['countryCode']);
        $address->setLatitude($homeAddress['latitude']);
        $address->setLongitude($homeAddress['longitude']);
        $address->setName($homeAddress['name']);
        $address->setHome(true);
        $address->setUser($user);
        $solidaryUser->setAddress($address);

        $solidaryUser->setComment($solidaryBeneficiary->getComment());
        $solidaryUser->setVehicle($solidaryBeneficiary->hasVehicule());

        // We set the link between User and SolidaryUser
        $user->setSolidaryUser($solidaryUser);

        
        // If there a Structure given, we use it. Otherwise we use the first admin structure
        $solidaryBeneficiaryStructure = $solidaryBeneficiary->getStructure();
        if (is_null($solidaryBeneficiaryStructure)) {
            // We get the Structure of the Admin to set the SolidaryUserStructure
            $structures = $this->structureRepository->findByUser($this->security->getUser());
            $structureAdmin = null;
            if (!is_null($structures) || count($structures)>0) {
                $solidaryBeneficiaryStructure = $structures[0];
            }
        }
        $solidaryUserStructure = new SolidaryUserStructure();
        $solidaryUserStructure->setStructure($solidaryBeneficiaryStructure);
        $solidaryUserStructure->setSolidaryUser($solidaryUser);

        if ($solidaryBeneficiary->isValidatedCandidate()) {
            // Already accepted. We set the date a give the appropriate role to the user
            $solidaryUserStructure->setAcceptedDate(new \Datetime());
            // We add the role to the user
            $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_BENEFICIARY);
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);
        }

        // Proofs
        foreach ($solidaryBeneficiary->getProofs() as $givenProof) {
            // We get the structure proof and we create a proof to persist
            $structureProofId = null;
            if (strrpos($givenProof['id'], '/')) {
                $structureProofId = substr($givenProof['id'], strrpos($givenProof['id'], '/') + 1);
            }
                
            $structureProof = $this->structureProofRepository->find($structureProofId);
            if (!is_null($structureProof) && isset($givenProof['value']) && !is_null($givenProof['value'])) {
                $proof = new Proof();
                $proof->setStructureProof($structureProof);
                $proof->setValue($givenProof['value']);
                $solidaryUserStructure->addProof($proof);
            }
        }

        $solidaryUser->addSolidaryUserStructure($solidaryUserStructure);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // dispatch SolidaryUser event
        $event = new SolidaryUserCreatedEvent($user, $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryUserCreatedEvent::NAME, $event);
        $event = new SolidaryCreatedEvent($user, $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);

        return $this->getSolidaryBeneficiary($user->getSolidaryUser()->getId());
    }


    /**
     * Update a SolidaryBeneficiary
     * For now, only accept/refuse and add a proof. Other fields are ignored.
     *
     * @param SolidaryBeneficiary $solidaryBeneficiary
     * @return SolidaryBeneficiary
     */
    public function updateSolidaryBeneficiary(SolidaryBeneficiary $solidaryBeneficiary): SolidaryBeneficiary
    {
        // We get the SolidaryUser and the User
        $solidaryUser = $this->solidaryUserRepository->find($solidaryBeneficiary->getId());
        $user = $solidaryUser->getUser();

        if (is_null($user)) {
            throw new SolidaryException(SolidaryException::UNKNOWN_USER);
        }

        

        // Accepted/Refused
        if (is_null($solidaryBeneficiary->isValidatedCandidate())) {
            // Don't do anything, it's not an acceptation or refulsal action
        } elseif (!$solidaryBeneficiary->isValidatedCandidate()) {
            // We change the status of the SolidaryUserStructure
            $this->acceptOrRefuseCandidate($solidaryUser, false, true, $solidaryBeneficiary->getStructure());
        } elseif ($solidaryBeneficiary->isValidatedCandidate()) {
            // We change the status of the SolidaryUserStructure
            $this->acceptOrRefuseCandidate($solidaryUser, true, false, $solidaryBeneficiary->getStructure());
        }
        
        // Proofs
        $this->addProofToSolidaryUser($solidaryUser, $solidaryBeneficiary->getProofs());

        $this->entityManager->persist($user);
        $this->entityManager->flush();



        return $this->getSolidaryBeneficiary($solidaryUser->getId());
    }

    /**
     * Create a SolidaryUser and its User if necessary from a SolidaryVolunteer
     *
     * @param SolidaryVolunteer $solidaryVolunteer
     * @return SolidaryVolunteer|null
     */
    public function createSolidaryVolunteer(SolidaryVolunteer $solidaryVolunteer): ?SolidaryVolunteer
    {
        // If there is no User, we need to create it first
        $user = $solidaryVolunteer->getUser();
        if (is_null($user)) {

            // We check if there no User with this email
            if (!is_null($this->userRepository->findOneBy(['email'=>$solidaryVolunteer->getEmail()]))) {
                throw new SolidaryException(SolidaryException::ALREADY_USER);
            }


            $user = new User();
            $user->setEmail($solidaryVolunteer->getEmail());
            $user->setGivenName($solidaryVolunteer->getGivenName());
            $user->setFamilyName($solidaryVolunteer->getFamilyName());
            $user->setNewsSubscription($solidaryVolunteer->hasNewsSubscription());
            $user->setTelephone($solidaryVolunteer->getTelephone());
            $user->setBirthDate($solidaryVolunteer->getBirthDate());
            $user->setGender($solidaryVolunteer->getGender());

            $user->setPhoneDisplay(1);
            $user->setSmoke($this->params['smoke']);
            $user->setMusic($this->params['music']);
            $user->setChat($this->params['chat']);
            // To do : Dynamic Language
            $user->setLanguage('fr_FR');

            // Set an encrypted password
            $password = $this->randomPassword();
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $user->setClearPassword($password); // Used to be send by email (not persisted)

            // auto valid the registration
            $user->setValidatedDate(new \DateTime());
        } else {
            // We check if this User does'nt already have a Solidary User
            if (!is_null($user->getSolidaryUser())) {
                throw new SolidaryException(SolidaryException::ALREADY_SOLIDARY_USER);
            }
        }

        $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_VOLUNTEER_CANDIDATE);
        $userAuthAssignment = new UserAuthAssignment();
        $userAuthAssignment->setAuthItem($authItem);
        $user->addUserAuthAssignment($userAuthAssignment);

        // We create the SolidaryUser
        $solidaryUser = new SolidaryUser();
        $solidaryUser->setVolunteer(true);
        $homeAddress = $solidaryVolunteer->getHomeAddress();
        $address = new Address();
        $address->setStreetAddress($homeAddress['streetAddress']);
        $address->setAddressLocality($homeAddress['addressLocality']);
        $address->setLocalAdmin($homeAddress['localAdmin']);
        $address->setCounty($homeAddress['county']);
        $address->setMacroCounty($homeAddress['macroCounty']);
        $address->setRegion($homeAddress['region']);
        $address->setMacroRegion($homeAddress['macroRegion']);
        $address->setAddressCountry($homeAddress['addressCountry']);
        $address->setCountryCode($homeAddress['countryCode']);
        $address->setLatitude($homeAddress['latitude']);
        $address->setLongitude($homeAddress['longitude']);
        $address->setName($homeAddress['name']);
        $address->setHome(true);
        $address->setUser($user);
        $solidaryUser->setAddress($address);

        $solidaryUser->setComment($solidaryVolunteer->getComment());

        // We set the link between User and SolidaryUser
        $user->setSolidaryUser($solidaryUser);

        
        // If there a Structure given, we use it. Otherwise we use the first admin structure
        $solidaryVolunteerStructure = $solidaryVolunteer->getStructure();
        if (is_null($solidaryVolunteerStructure)) {
            // We get the Structure of the Admin to set the SolidaryUserStructure
            $structures = $this->structureRepository->findByUser($this->security->getUser());
           
            $structureAdmin = null;
            if (!is_null($structures) || count($structures)>0) {
                $solidaryVolunteerStructure = $structures[0];
            }
        }
        $solidaryUserStructure = new SolidaryUserStructure();
        $solidaryUserStructure->setStructure($solidaryVolunteerStructure);
        $solidaryUserStructure->setSolidaryUser($solidaryUser);

        if ($solidaryVolunteer->isValidatedCandidate()) {
            // Already accepted. We set the date a give the appropriate role to the user
            $solidaryUserStructure->setAcceptedDate(new \Datetime());
            // We add the role to the user
            $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_VOLUNTEER);
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);
        }

        $solidaryUser->addSolidaryUserStructure($solidaryUserStructure);

        // Availabilities : First we set those given, next we fill the blanks with the structure default
        
        if (!is_null($solidaryVolunteer->getMMinTime())) {
            $solidaryUser->setMMinTime($solidaryVolunteer->getMMinTime());
        }
        if (!is_null($solidaryVolunteer->getMMaxTime())) {
            $solidaryUser->setMMaxTime($solidaryVolunteer->getMMaxTime());
        }
        if (!is_null($solidaryVolunteer->getAMinTime())) {
            $solidaryUser->setAMinTime($solidaryVolunteer->getAMinTime());
        }
        if (!is_null($solidaryVolunteer->getAMaxTime())) {
            $solidaryUser->setAMaxTime($solidaryVolunteer->getAMaxTime());
        }
        if (!is_null($solidaryVolunteer->getEMinTime())) {
            $solidaryUser->setEMinTime($solidaryVolunteer->getEMinTime());
        }
        if (!is_null($solidaryVolunteer->getEMaxTime())) {
            $solidaryUser->setEMaxTime($solidaryVolunteer->getEMaxTime());
        }
        
        if (!is_null($solidaryVolunteer->hasMMon())) {
            $solidaryUser->setMMon($solidaryVolunteer->hasMMon());
        }
        if (!is_null($solidaryVolunteer->hasMTue())) {
            $solidaryUser->setMTue($solidaryVolunteer->hasMTue());
        }
        if (!is_null($solidaryVolunteer->hasMWed())) {
            $solidaryUser->setMWed($solidaryVolunteer->hasMWed());
        }
        if (!is_null($solidaryVolunteer->hasMThu())) {
            $solidaryUser->setMThu($solidaryVolunteer->hasMThu());
        }
        if (!is_null($solidaryVolunteer->hasMFri())) {
            $solidaryUser->setMFri($solidaryVolunteer->hasMFri());
        }
        if (!is_null($solidaryVolunteer->hasMSat())) {
            $solidaryUser->setMSat($solidaryVolunteer->hasMSat());
        }
        if (!is_null($solidaryVolunteer->hasMSun())) {
            $solidaryUser->setMSun($solidaryVolunteer->hasMSun());
        }
        if (!is_null($solidaryVolunteer->hasAMon())) {
            $solidaryUser->setAMon($solidaryVolunteer->hasAMon());
        }
        if (!is_null($solidaryVolunteer->hasATue())) {
            $solidaryUser->setATue($solidaryVolunteer->hasATue());
        }
        if (!is_null($solidaryVolunteer->hasAWed())) {
            $solidaryUser->setAWed($solidaryVolunteer->hasAWed());
        }
        if (!is_null($solidaryVolunteer->hasAThu())) {
            $solidaryUser->setAThu($solidaryVolunteer->hasAThu());
        }
        if (!is_null($solidaryVolunteer->hasAFri())) {
            $solidaryUser->setAFri($solidaryVolunteer->hasAFri());
        }
        if (!is_null($solidaryVolunteer->hasASat())) {
            $solidaryUser->setASat($solidaryVolunteer->hasASat());
        }
        if (!is_null($solidaryVolunteer->hasASun())) {
            $solidaryUser->setASun($solidaryVolunteer->hasASun());
        }
        if (!is_null($solidaryVolunteer->hasEMon())) {
            $solidaryUser->setEMon($solidaryVolunteer->hasEMon());
        }
        if (!is_null($solidaryVolunteer->hasETue())) {
            $solidaryUser->setETue($solidaryVolunteer->hasETue());
        }
        if (!is_null($solidaryVolunteer->hasEWed())) {
            $solidaryUser->setEWed($solidaryVolunteer->hasEWed());
        }
        if (!is_null($solidaryVolunteer->hasEThu())) {
            $solidaryUser->setEThu($solidaryVolunteer->hasEThu());
        }
        if (!is_null($solidaryVolunteer->hasEFri())) {
            $solidaryUser->setEFri($solidaryVolunteer->hasEFri());
        }
        if (!is_null($solidaryVolunteer->hasESat())) {
            $solidaryUser->setESat($solidaryVolunteer->hasESat());
        }
        if (!is_null($solidaryVolunteer->hasESun())) {
            $solidaryUser->setESun($solidaryVolunteer->hasESun());
        }

        // Default values
        $this->userManager->setDefaultSolidaryUserAvailabilities($solidaryUser, $solidaryVolunteerStructure);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // // dispatch SolidaryUser event
        $event = new SolidaryUserCreatedEvent($user, $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryUserCreatedEvent::NAME, $event);
        $event = new SolidaryCreatedEvent($user, $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);
        
        return $this->getSolidaryVolunteer($user->getSolidaryUser()->getId());
    }

    /**
     * Update a SolidaryVolunteer
     * For now, only accept/refuse and update the availabilities. Other fields are ignored.
     *
     * @param SolidaryVolunteer $solidaryVolunteer
     * @return SolidaryVolunteer
     */
    public function updateSolidaryVolunteer(SolidaryVolunteer $solidaryVolunteer): SolidaryVolunteer
    {
        
        // We get the SolidaryUser and the User
        $solidaryUser = $this->solidaryUserRepository->find($solidaryVolunteer->getId());
        $user = $solidaryUser->getUser();

        if (is_null($user)) {
            throw new SolidaryException(SolidaryException::UNKNOWN_USER);
        }

        $solidaryUser = $user->getSolidaryUser();

        // Accepted/Refused
        if (is_null($solidaryVolunteer->isValidatedCandidate())) {
            // Don't do anything, it's not an acceptation or refulsal action
        } elseif (!$solidaryVolunteer->isValidatedCandidate()) {
            // We change the status of the SolidaryUserStructure
            $this->acceptOrRefuseCandidate($solidaryUser, false, true, $solidaryVolunteer->getStructure());
        } elseif ($solidaryVolunteer->isValidatedCandidate()) {
            // We change the status of the SolidaryUserStructure
            $this->acceptOrRefuseCandidate($solidaryUser, true, false, $solidaryVolunteer->getStructure());
        }
        
        if (!is_null($solidaryVolunteer->getMMinTime())) {
            $solidaryUser->setMMinTime($solidaryVolunteer->getMMinTime());
        }
        if (!is_null($solidaryVolunteer->getMMaxTime())) {
            $solidaryUser->setMMaxTime($solidaryVolunteer->getMMaxTime());
        }
        if (!is_null($solidaryVolunteer->getAMinTime())) {
            $solidaryUser->setAMinTime($solidaryVolunteer->getAMinTime());
        }
        if (!is_null($solidaryVolunteer->getAMaxTime())) {
            $solidaryUser->setAMaxTime($solidaryVolunteer->getAMaxTime());
        }
        if (!is_null($solidaryVolunteer->getEMinTime())) {
            $solidaryUser->setEMinTime($solidaryVolunteer->getEMinTime());
        }
        if (!is_null($solidaryVolunteer->getEMaxTime())) {
            $solidaryUser->setEMaxTime($solidaryVolunteer->getEMaxTime());
        }
        
        if (!is_null($solidaryVolunteer->hasMMon())) {
            $solidaryUser->setMMon($solidaryVolunteer->hasMMon());
        }
        if (!is_null($solidaryVolunteer->hasMTue())) {
            $solidaryUser->setMTue($solidaryVolunteer->hasMTue());
        }
        if (!is_null($solidaryVolunteer->hasMWed())) {
            $solidaryUser->setMWed($solidaryVolunteer->hasMWed());
        }
        if (!is_null($solidaryVolunteer->hasMThu())) {
            $solidaryUser->setMThu($solidaryVolunteer->hasMThu());
        }
        if (!is_null($solidaryVolunteer->hasMFri())) {
            $solidaryUser->setMFri($solidaryVolunteer->hasMFri());
        }
        if (!is_null($solidaryVolunteer->hasMSat())) {
            $solidaryUser->setMSat($solidaryVolunteer->hasMSat());
        }
        if (!is_null($solidaryVolunteer->hasMSun())) {
            $solidaryUser->setMSun($solidaryVolunteer->hasMSun());
        }
        if (!is_null($solidaryVolunteer->hasAMon())) {
            $solidaryUser->setAMon($solidaryVolunteer->hasAMon());
        }
        if (!is_null($solidaryVolunteer->hasATue())) {
            $solidaryUser->setATue($solidaryVolunteer->hasATue());
        }
        if (!is_null($solidaryVolunteer->hasAWed())) {
            $solidaryUser->setAWed($solidaryVolunteer->hasAWed());
        }
        if (!is_null($solidaryVolunteer->hasAThu())) {
            $solidaryUser->setAThu($solidaryVolunteer->hasAThu());
        }
        if (!is_null($solidaryVolunteer->hasAFri())) {
            $solidaryUser->setAFri($solidaryVolunteer->hasAFri());
        }
        if (!is_null($solidaryVolunteer->hasASat())) {
            $solidaryUser->setASat($solidaryVolunteer->hasASat());
        }
        if (!is_null($solidaryVolunteer->hasASun())) {
            $solidaryUser->setASun($solidaryVolunteer->hasASun());
        }
        if (!is_null($solidaryVolunteer->hasEMon())) {
            $solidaryUser->setEMon($solidaryVolunteer->hasEMon());
        }
        if (!is_null($solidaryVolunteer->hasETue())) {
            $solidaryUser->setETue($solidaryVolunteer->hasETue());
        }
        if (!is_null($solidaryVolunteer->hasEWed())) {
            $solidaryUser->setEWed($solidaryVolunteer->hasEWed());
        }
        if (!is_null($solidaryVolunteer->hasEThu())) {
            $solidaryUser->setEThu($solidaryVolunteer->hasEThu());
        }
        if (!is_null($solidaryVolunteer->hasEFri())) {
            $solidaryUser->setEFri($solidaryVolunteer->hasEFri());
        }
        if (!is_null($solidaryVolunteer->hasESat())) {
            $solidaryUser->setESat($solidaryVolunteer->hasESat());
        }
        if (!is_null($solidaryVolunteer->hasESun())) {
            $solidaryUser->setESun($solidaryVolunteer->hasESun());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->getSolidaryVolunteer($solidaryUser->getId());
    }

    /**
     * Accept or refuse a SolidaryUser for a Structure (given or the admin's)
     *
     * @param SolidaryUser $solidaryUser   The SolidaryUser
     * @param boolean $acceptCandidate     Accept this SolidaryUser for a Structure to be determined
     * @param boolean $refuseCandidate     Refuse this SolidaryUser for a Structure to be determined
     * @param Structure $structure         The structure (if there is no structure we use the admin one)
     * @return void
     */
    public function acceptOrRefuseCandidate(SolidaryUser $solidaryUser, bool $acceptCandidate = false, bool $refuseCandidate = false, Structure $structure = null)
    {
        // Handle the status of the candidate
        $solidaryUserStructures = $solidaryUser->getSolidaryUserStructures();

        // If there a Structure given, we use it. Otherwise we use the first admin structure
        if (is_null($structure)) {
            // We get the Structure of the Admin to set the SolidaryUserStructure
            $structures = $this->structureRepository->findByUser($this->security->getUser());
            if (!is_null($structures) || count($structures)>0) {
                $structure = $structures[0];
            }
        }
        
        // We search the right solidaryUserStructure to update
        $solidaryUserStructureToUpdate = null;
        foreach ($solidaryUserStructures as $solidaryUserStructure) {
            if ($solidaryUserStructure->getStructure()->getId() == $structure->getId()) {
                $solidaryUserStructureToUpdate = $solidaryUserStructure;
                break;
            }
        }


        if ($acceptCandidate && $solidaryUserStructureToUpdate->getAcceptedDate()=="" && $solidaryUserStructureToUpdate->getRefusedDate()=="") {

            // We check if this candidate has already been accepted or refused
            if (!is_null($solidaryUserStructureToUpdate->getAcceptedDate())) {
                throw new SolidaryException(SolidaryException::ALREADY_ACCEPTED);
            }


            $solidaryUserStructureToUpdate->setAcceptedDate(new \DateTime());
            
            // We add the role to the user
            if ($solidaryUser->isVolunteer()) {
                $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_VOLUNTEER);
            } elseif ($solidaryUser->isBeneficiary()) {
                $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_BENEFICIARY);
            } else {
                throw new SolidaryException(SolidaryException::NO_ROLE);
            }
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user = $solidaryUser->getUser();
            $user->addUserAuthAssignment($userAuthAssignment);

            // We dispatch the event
            $event = new SolidaryUserStructureAcceptedEvent($solidaryUserStructureToUpdate, $this->security->getUser());
            $this->eventDispatcher->dispatch(SolidaryUserStructureAcceptedEvent::NAME, $event);
        } elseif ($refuseCandidate && $solidaryUserStructureToUpdate->getAcceptedDate()=="" && $solidaryUserStructureToUpdate->getRefusedDate()=="") {
            if (!is_null($solidaryUserStructureToUpdate->getRefusedDate())) {
                throw new SolidaryException(SolidaryException::ALREADY_REFUSED);
            }
    
            $solidaryUserStructureToUpdate->setRefusedDate(new \DateTime());

            // We dispatch the event
            $event = new SolidaryUserStructureRefusedEvent($solidaryUserStructureToUpdate, $this->security->getUser());
            $this->eventDispatcher->dispatch(SolidaryUserStructureRefusedEvent::NAME, $event);
        }
    }
    
    /**
     * Add Proofs to an existing SolidaryUserStructure of a SolidaryUser (for a given Structure or not)
     *
     * @param SolidaryUser $solidaryUser    The SolidaryUser
     * @param array $proofs                 The proofs to add
     * @param Structure $structure          The Structure  (if there is no structure we use the admin one)
     * @return void
     */
    public function addProofToSolidaryUser(SolidaryUser $solidaryUser, array $proofs, Structure $structure=null)
    {
        $solidaryUserStructures = $solidaryUser->getSolidaryUserStructures();

        // If there a Structure given, we use it. Otherwise we use the first admin structure
        if (is_null($structure)) {
            // We get the Structure of the Admin to set the SolidaryUserStructure
            $structures = $this->structureRepository->findByUser($this->security->getUser());
            if (!is_null($structures) || count($structures)>0) {
                $structure = $structures[0];
            }
        }
        
        // We search the right solidaryUserStructure to update
        $solidaryUserStructureToUpdate = null;
        foreach ($solidaryUserStructures as $solidaryUserStructure) {
            if ($solidaryUserStructure->getStructure()->getId() == $structure->getId()) {
                $solidaryUserStructureToUpdate = $solidaryUserStructure;
                break;
            }
        }

        // We get the existing proofs of this SolidaryUserStructure to check if we don't try add an already existing proof
        $existingProofs = $solidaryUserStructureToUpdate->getProofs();

        // We add the new proofs to the SolidaryUserStructure
        foreach ($proofs as $givenProof) {
            // We get the structure proof and we create a proof to persist
            $structureProofId = null;
            if (strrpos($givenProof['id'], '/')) {
                $structureProofId = substr($givenProof['id'], strrpos($givenProof['id'], '/') + 1);
            }
                
            $structureProof = $this->structureProofRepository->find($structureProofId);

            if (!is_null($structureProof) && isset($givenProof['value']) && !is_null($givenProof['value'])) {

                // We check if there is already a similar proof
                $alreadyExistingProof = null;
                foreach ($existingProofs as $existingProof) {
                    if ($existingProof->getStructureProof()->getId() == $structureProofId) {
                        $alreadyExistingProof = $existingProof;
                    }
                }

                // New Proof, we create it
                if (is_null($alreadyExistingProof)) {
                    $proof = new Proof();
                    $proof->setStructureProof($structureProof);
                    $proof->setValue($givenProof['value']);
                    $solidaryUserStructureToUpdate->addProof($proof);
                } else {
                    // Existing proof, we update the value
                    $existingProof->setValue($givenProof['value']);
                }
            }
        }
    }


    /**
     * Genereate a random password
     *
     * @return String
     */
    private function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
