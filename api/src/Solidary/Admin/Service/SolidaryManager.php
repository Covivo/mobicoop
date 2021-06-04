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
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Auth\Repository\UserAuthAssignmentRepository;
use App\Carpool\Entity\Criteria;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Geography\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Entity\Solidary;
use App\Solidary\Admin\Exception\SolidaryException;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\Structure;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\User\Entity\User;
use App\User\Admin\Service\UserManager;
use App\User\Repository\UserRepository;
use DateTime;
use DateInterval;
use Symfony\Component\Security\Core\Security;

/**
 * Solidary manager in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryManager
{
    /**
     * @var User $poster
     */
    private $poster;
    private $entityManager;
    private $userManager;
    private $adManager;
    private $userRepository;
    private $structureProofRepository;
    private $solidaryUserRepository;
    private $structureRepository;
    private $solidaryUserStructureRepository;
    private $proposalRepository;
    private $authItemRepository;
    private $userAuthAssignmentRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        UserManager $userManager,
        AdManager $adManager,
        UserRepository $userRepository,
        StructureProofRepository $structureProofRepository,
        SolidaryUserRepository $solidaryUserRepository,
        StructureRepository $structureRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        ProposalRepository $proposalRepository,
        AuthItemRepository $authItemRepository,
        UserAuthAssignmentRepository $userAuthAssignmentRepository
    ) {
        $this->poster = $security->getUser();
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->adManager = $adManager;
        $this->userRepository = $userRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->structureRepository = $structureRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->proposalRepository = $proposalRepository;
        $this->authItemRepository = $authItemRepository;
        $this->userAuthAssignmentRepository = $userAuthAssignmentRepository;
    }

    /**
     * Get Solidary records
     *
     * @param PaginatorInterface $solidaries  The solidary objects
     * @return array|null The solidary records
     */
    public function getSolidaries(PaginatorInterface $solidaries)
    {
        return $solidaries;
    }

    /**
     * Add a solidary record.
     *
     * @param Solidary      $solidary               The solidary to add
     * @param array         $fields                 The fields
     * @return Solidary     The solidary created
     */
    public function addSolidary(Solidary $solidary, array $fields)
    {
        // To create a new Solidary record, we need the following steps :
        // 1. create a SolidaryUser if the beneficiary is not already a solidary user
        // 2. create a SolidaryUserStructure, reflecting the status of the beneficiary within the structure (if the beneficiary has not already a SolidaryRecord in that structure)
        // 3. create SolidaryProofs if needed, linked to the SolidaryUserStructure
        // 4. create a Proposal, reflecting the journey needed for the beneficiary
        // 5. create a SolidaryRecord, linked to the SolidaryUserStructure, the Proposal, a Subject and Needs


        // first we perform some checkings !

        // check beneficiary
        if (!isset($fields['beneficiary'])) {
            throw new SolidaryException(SolidaryException::BENEFICIARY_REQUIRED);
        }

        // check structure
        if (!isset($fields['structure'])) {
            throw new SolidaryException(SolidaryException::STRUCTURE_REQUIRED);
        }
        if (!$structure = $this->structureRepository->find($fields['structure'])) {
            throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_NOT_FOUND, $fields['structure']));
        }

        // check journey
        if (!isset($fields['origin'])) {
            throw new SolidaryException(SolidaryException::ORIGIN_REQUIRED);
        }
        if (!isset($fields['destination']) && (!isset($fields['destinationAny']) || !$fields['destinationAny'])) {
            throw new SolidaryException(SolidaryException::DESTINATION_REQUIRED);
        }

        // check frequency
        if (!isset($fields['regular'])) {
            throw new SolidaryException(SolidaryException::FREQUENCY_REQUIRED);
        }
        if ($fields['regular'] && !isset($fields['regularSchedules'])) {
            throw new SolidaryException(SolidaryException::REGULAR_SCHEDULES_REQUIRED);
        }
        if (!$fields['regular'] && !isset($fields['punctualOutwardDateChoice'])) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_DATE_CHOICE_REQUIRED);
        }
        if (!$fields['regular'] && !in_array($fields['punctualOutwardDateChoice'], Solidary::PUNCTUAL_OUTWARD_DATE_CHOICES)) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_DATE_CHOICE_INVALID);
        }
        if (!$fields['regular'] && !isset($fields['punctualOutwardTimeChoice'])) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_TIME_CHOICE_REQUIRED);
        }
        if (!$fields['regular'] && !in_array($fields['punctualOutwardTimeChoice'], Solidary::PUNCTUAL_TIME_CHOICES)) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_TIME_CHOICE_INVALID);
        }



        // 1 - create the SolidaryUser
        $beneficiary = null;
        if (isset($fields['beneficiary']['id'])) {
            // check beneficiary is a valid user
            if (!$beneficiary = $this->userRepository->find($fields['beneficiary']['id'])) {
                throw new SolidaryException(sprintf(SolidaryException::BENEFICIARY_NOT_FOUND, $fields['beneficiary']['id']));
            }
            // check if beneficiary informations have been updated
            if (isset($fields['beneficiary']['givenName']) && $fields['beneficiary']['givenName'] != $beneficiary->getGivenName()) {
                $beneficiary->setGivenName($fields['beneficiary']['givenName']);
            }
            if (isset($fields['beneficiary']['familyName']) && $fields['beneficiary']['familyName'] != $beneficiary->getFamilyName()) {
                $beneficiary->setFamilyName($fields['beneficiary']['familyName']);
            }
            if (isset($fields['beneficiary']['email']) && $fields['beneficiary']['email'] != $beneficiary->getEmail()) {
                $beneficiary->setEmail($fields['beneficiary']['email']);
            }
            if (isset($fields['beneficiary']['telephone']) && $fields['beneficiary']['telephone'] != $beneficiary->getTelephone()) {
                $beneficiary->setTelephone($fields['beneficiary']['telephone']);
            }
            if (isset($fields['beneficiary']['gender']) && $fields['beneficiary']['gender'] != $beneficiary->getGender()) {
                $beneficiary->setGender($fields['beneficiary']['gender']);
            }
            if (isset($fields['beneficiary']['birthDate']) && $fields['beneficiary']['birthDate'] != $beneficiary->getBirthDate()) {
                $beneficiary->setBirthDate(new DateTime($fields['beneficiary']['birthDate']));
            }
            // check if beneficiary home address has been updated
            if (isset($fields['beneficiary']['homeAddress'])) {
                // we search the original home address
                $homeAddress = null;
                foreach ($beneficiary->getAddresses() as $address) {
                    if ($address->isHome()) {
                        $homeAddress = $address;
                        break;
                    }
                }
                if (!is_null($homeAddress)) {
                    // we have to update each field...
                    /**
                    * @var Address $homeAddress
                    */
                    $updated = false;
                    if (isset($fields['beneficiary']['homeAddress']['streetAddress']) && $homeAddress->getStreetAddress() != $fields['beneficiary']['homeAddress']['streetAddress']) {
                        $updated = true;
                        $homeAddress->setStreetAddress($fields['beneficiary']['homeAddress']['streetAddress']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['postalCode']) && $homeAddress->getPostalCode() != $fields['beneficiary']['homeAddress']['postalCode']) {
                        $updated = true;
                        $homeAddress->setPostalCode($fields['beneficiary']['homeAddress']['postalCode']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['addressLocality']) && $homeAddress->getAddressLocality() != $fields['beneficiary']['homeAddress']['addressLocality']) {
                        $updated = true;
                        $homeAddress->setAddressLocality($fields['beneficiary']['homeAddress']['addressLocality']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['addressCountry']) && $homeAddress->getAddressCountry() != $fields['beneficiary']['homeAddress']['addressCountry']) {
                        $updated = true;
                        $homeAddress->setAddressCountry($fields['beneficiary']['homeAddress']['addressCountry']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['latitude']) && $homeAddress->getLatitude() != $fields['beneficiary']['homeAddress']['latitude']) {
                        $updated = true;
                        $homeAddress->setLatitude($fields['beneficiary']['homeAddress']['latitude']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['longitude']) && $homeAddress->getLongitude() != $fields['beneficiary']['homeAddress']['longitude']) {
                        $updated = true;
                        $homeAddress->setLongitude($fields['beneficiary']['homeAddress']['longitude']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['houseNumber']) && $homeAddress->getHouseNumber() != $fields['beneficiary']['homeAddress']['houseNumber']) {
                        $updated = true;
                        $homeAddress->setHouseNumber($fields['beneficiary']['homeAddress']['houseNumber']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['subLocality']) && $homeAddress->getSubLocality() != $fields['beneficiary']['homeAddress']['subLocality']) {
                        $updated = true;
                        $homeAddress->setSubLocality($fields['beneficiary']['homeAddress']['subLocality']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['localAdmin']) && $homeAddress->getLocalAdmin() != $fields['beneficiary']['homeAddress']['localAdmin']) {
                        $updated = true;
                        $homeAddress->setLocalAdmin($fields['beneficiary']['homeAddress']['localAdmin']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['county']) && $homeAddress->getCounty() != $fields['beneficiary']['homeAddress']['county']) {
                        $updated = true;
                        $homeAddress->setCounty($fields['beneficiary']['homeAddress']['county']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['macroCounty']) && $homeAddress->getMacroCounty() != $fields['beneficiary']['homeAddress']['macroCounty']) {
                        $updated = true;
                        $homeAddress->setMacroCounty($fields['beneficiary']['homeAddress']['macroCounty']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['region']) && $homeAddress->getRegion() != $fields['beneficiary']['homeAddress']['region']) {
                        $updated = true;
                        $homeAddress->setRegion($fields['beneficiary']['homeAddress']['region']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['macroRegion']) && $homeAddress->getMacroRegion() != $fields['beneficiary']['homeAddress']['macroRegion']) {
                        $updated = true;
                        $homeAddress->setMacroRegion($fields['beneficiary']['homeAddress']['macroRegion']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['countryCode']) && $homeAddress->getCountryCode() != $fields['beneficiary']['homeAddress']['countryCode']) {
                        $updated = true;
                        $homeAddress->setCountryCode($fields['beneficiary']['homeAddress']['countryCode']);
                    }
                    if ($updated) {
                        $this->entityManager->persist($homeAddress);
                    }
                }
            }
        } else {
            // new user
            $beneficiary = $this->userManager->createUserFromArray($fields['beneficiary']);
        }
        // add the solidary role if not already granted
        $authItem = $this->authItemRepository->find($structure->hasBeneficiaryAutoApproval() ? AuthItem::ROLE_SOLIDARY_BENEFICIARY : AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE);
        if (!$this->userAuthAssignmentRepository->findByAuthItemAndUser($authItem, $beneficiary)) {
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $beneficiary->addUserAuthAssignment($userAuthAssignment);
        }
        $this->entityManager->persist($beneficiary);

        // check if the beneficiary is already a SolidaryUser
        $solidaryUser = null;
        if (isset($fields['beneficiary']['id']) && !$solidaryUser = $this->solidaryUserRepository->findByUserId($fields['beneficiary']['id'])) {
            // not already a solidary user, we need to create a new one
            $solidaryUser = new SolidaryUser();
            $solidaryUser->setBeneficiary(true);
            $solidaryUser->setUser($beneficiary);
            $solidaryUser->setAddress($beneficiary->getHomeAddress());
            $solidaryUser->setMaxDistance(SolidaryUser::DEFAULT_MAX_DISTANCE);
        } else {
            // already a solidary user, we ensure that it is beneficiary
            $solidaryUser->setBeneficiary(true);
        }
        $this->entityManager->persist($solidaryUser);

        
        // 2 - create the SolidaryUserStructure
        $solidaryUserStructure = null;
        $createSolidaryUserStructure = false;
        if ($solidaryUser->getId() && !$solidaryUserStructure = $this->solidaryUserStructureRepository->findByStructureAndSolidaryUser($structure->getId(), $solidaryUser->getId())) {
            $createSolidaryUserStructure = true;
        } elseif (!$solidaryUser->getId()) {
            $createSolidaryUserStructure = true;
        }

        if ($createSolidaryUserStructure) {
            // no SolidaryUserStructureFound, we need to create a new one
            $solidaryUserStructure = new SolidaryUserStructure();
            $solidaryUserStructure->setSolidaryUser($solidaryUser);
            $solidaryUserStructure->setStructure($structure);
            $solidaryUserStructure->setStatus($structure->hasBeneficiaryAutoApproval() ? SolidaryUserStructure::STATUS_ACCEPTED : SolidaryUserStructure::STATUS_PENDING);
            $this->entityManager->persist($solidaryUserStructure);
        }

        
        // 3 - create the proofs
        // we only set the basic proofs, not the files that would be sent on a separate call
        if (isset($fields['proofs'])) {
            foreach ($fields['proofs'] as $aproof) {
                if (!isset($aproof['id'])) {
                    throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_ID_REQUIRED);
                }
                // if (!isset($aproof['value'])) {
                //     throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_PROOF_VALUE_REQUIRED, $aproof['id']));
                // }
                if (!$structureProof = $this->structureProofRepository->find($aproof['id'])) {
                    throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_PROOF_NOT_FOUND, $aproof['id']));
                }
                if (is_null($aproof['value']) && $structureProof->isMandatory()) {
                    throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_VALUE_REQUIRED);
                }
                // we skip null values (meaning the proof is not mandatory and has been omitted during the edition)
                if (!$structureProof->isFile() && !is_null($aproof['value'])) {
                    $proof = new Proof();
                    $proof->setStructureProof($structureProof);
                    $proof->setValue($aproof['value']);
                    $solidaryUserStructure->addProof($proof);
                    $this->entityManager->persist($proof);
                }
            }
        }

        // we need to flush here has we are now about to post the ad => the users need to be persisted
        $this->entityManager->flush();
        
        // 4 - create the proposal
        $params = [
            'origin' => $fields['origin'],
            'destination' => isset($fields['destinationAny']) && $fields['destinationAny'] ? null : $fields['destination'],
            'regular' => $fields['regular'],
            'poster' => $this->poster->getId(),
            'beneficiary' => $beneficiary->getId()
        ];
        if (isset($fields['punctualOutwardMinDate'])) {
            $params['punctualOutwardMinDate'] = $fields['punctualOutwardMinDate'];
        }
        if (isset($fields['punctualOutwardMaxDate'])) {
            $params['punctualOutwardMaxDate'] = $fields['punctualOutwardMaxDate'];
        }
        if (isset($fields['punctualOutwardMinTime'])) {
            $params['punctualOutwardMinTime'] = $fields['punctualOutwardMinTime'];
        }
        if (isset($fields['punctualOutwardDateChoice'])) {
            $params['punctualOutwardDateChoice'] = $fields['punctualOutwardDateChoice'];
        }
        if (isset($fields['punctualOutwardTimeChoice'])) {
            $params['punctualOutwardTimeChoice'] = $fields['punctualOutwardTimeChoice'];
        }
        if (isset($fields['punctualReturnDateChoice'])) {
            $params['punctualReturnDateChoice'] = $fields['punctualReturnDateChoice'];
        }
        if (isset($fields['punctualReturnDate'])) {
            $params['punctualReturnDate'] = $fields['punctualReturnDate'];
        }
        if (isset($fields['punctualReturnTime'])) {
            $params['punctualReturnTime'] = $fields['punctualReturnTime'];
        }
        if (isset($fields['regularMinDate'])) {
            $params['regularMinDate'] = $fields['regularMinDate'];
        }
        if (isset($fields['regularMaxDate'])) {
            $params['regularMaxDate'] = $fields['regularMaxDate'];
        }
        if (isset($fields['regularSchedules'])) {
            $params['regularSchedules'] = $fields['regularSchedules'];
        }
        $ad = $this->createAdFromArray($params, $this->getTimeAndMarginForStructure($structure));
        $solidary->setProposal($this->proposalRepository->find($ad->getId()));


        // 5 - create the SolidaryRecord

        // set original frequency
        $solidary->setFrequency($fields['regular'] ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL);

        // set status
        $solidary->setStatus(Solidary::STATUS_ASKED);

        
        // persist the solidary record
        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        return $solidary;
    }

    /**
     * Patch a solidary record.
     *
     * @param Solidary      $solidary               The solidary to update
     * @param array         $fields                 The updated fields
     * @return Solidary     The solidary updated
     */
    public function patchSolidary(solidary $solidary, array $fields)
    {
        // persist the solidary record
        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        return $solidary;
    }

    /**
     * Delete a solidary record.
     *
     * @param Solidary      $solidary  The solidary to delete
     * @return void
     */
    public function deleteSolidary(Solidary $solidary)
    {
        $this->entityManager->remove($solidary);
        $this->entityManager->flush();
    }

    /**
     * Create an Ad from an array
     *
     * The array can contain the following informations :
     * - origin (mandatory)
     * - destination (mandatory, value can be null though)
     * - regular (T/F)
     * - punctualOutwardDateChoice :    1 = chosen outward date, 2 = in the next 7 days, 3 = in the next 15 days, 4 = in the next 30 days
     * - punctualOutwardTimeChoice :    1 = chosen outward time, 2 = between mMin and mMax, 3 = between aMin and aMax, 4 = between eMin and eMax
     * - punctualOutwardMinDate :       computed outward min date
     * - punctualOutwardMinTime :       computed outward min time
     * - punctualOutwardMaxDate :       computed outward max date
     * - punctualReturnDateChoice :     1 = no return, 2 = one hour later, 3 = 2 hours later, 4 = 3 hours later, 5 = chosen return date and time
     * - punctualReturnDate :           computed return date
     * - punctualReturnTime :           computed return time
     * - regularMinDate :               chosen regular min date
     * - regularDateChoice :            1 = for a week, 2 = for a month, 3 = till a given date
     * - regularMaxDate :               computed regular max date
     * - regularSchedules [
     *      days [
     *          1,2,3...7
     *      ],
     *      outwardTime,
     *      returnTime
     * ]
     *
     * @param array $aad                The ad informations as an array
     * @param array $times              The time ranges (depends on the structure)
     * @return Ad                       The Ad object
     */
    private function createAdFromArray(array $aad, array $times): Ad
    {
        $ad = new Ad();

        // users
        $ad->setPosterId($aad['poster']);
        $ad->setUserId($aad['beneficiary']);

        // origin & destination
        $origin = new Address();
        $destination = null;
        
        if (isset($aad['origin']['houseNumber'])) {
            $origin->setHouseNumber($aad['origin']['houseNumber']);
        }
        if (isset($aad['origin']['street'])) {
            $origin->setStreet($aad['origin']['street']);
        }
        if (isset($aad['origin']['streetAddress'])) {
            $origin->setStreetAddress($aad['origin']['streetAddress']);
        }
        if (isset($aad['origin']['postalCode'])) {
            $origin->setPostalCode($aad['origin']['postalCode']);
        }
        if (isset($aad['origin']['subLocality'])) {
            $origin->setSubLocality($aad['origin']['subLocality']);
        }
        if (isset($aad['origin']['addressLocality'])) {
            $origin->setAddressLocality($aad['origin']['addressLocality']);
        }
        if (isset($aad['origin']['localAdmin'])) {
            $origin->setLocalAdmin($aad['origin']['localAdmin']);
        }
        if (isset($aad['origin']['county'])) {
            $origin->setCounty($aad['origin']['county']);
        }
        if (isset($aad['origin']['macroCounty'])) {
            $origin->setMacroCounty($aad['origin']['macroCounty']);
        }
        if (isset($aad['origin']['region'])) {
            $origin->setRegion($aad['origin']['region']);
        }
        if (isset($aad['origin']['macroRegion'])) {
            $origin->setMacroRegion($aad['origin']['macroRegion']);
        }
        if (isset($aad['origin']['addressCountry'])) {
            $origin->setAddressCountry($aad['origin']['addressCountry']);
        }
        if (isset($aad['origin']['countryCode'])) {
            $origin->setCountryCode($aad['origin']['countryCode']);
        }
        if (isset($aad['origin']['latitude'])) {
            $origin->setLatitude($aad['origin']['latitude']);
        }
        if (isset($aad['origin']['longitude'])) {
            $origin->setLongitude($aad['origin']['longitude']);
        }
        
        if ($aad['destination']) {
            $destination = new Address();
            if (isset($aad['destination']['houseNumber'])) {
                $destination->setHouseNumber($aad['destination']['houseNumber']);
            }
            if (isset($aad['destination']['street'])) {
                $destination->setStreet($aad['destination']['street']);
            }
            if (isset($aad['destination']['streetAddress'])) {
                $destination->setStreetAddress($aad['destination']['streetAddress']);
            }
            if (isset($aad['destination']['postalCode'])) {
                $destination->setPostalCode($aad['destination']['postalCode']);
            }
            if (isset($aad['destination']['subLocality'])) {
                $destination->setSubLocality($aad['destination']['subLocality']);
            }
            if (isset($aad['destination']['addressLocality'])) {
                $destination->setAddressLocality($aad['destination']['addressLocality']);
            }
            if (isset($aad['destination']['localAdmin'])) {
                $destination->setLocalAdmin($aad['destination']['localAdmin']);
            }
            if (isset($aad['destination']['county'])) {
                $destination->setCounty($aad['destination']['county']);
            }
            if (isset($aad['destination']['macroCounty'])) {
                $destination->setMacroCounty($aad['destination']['macroCounty']);
            }
            if (isset($aad['destination']['region'])) {
                $destination->setRegion($aad['destination']['region']);
            }
            if (isset($aad['destination']['macroRegion'])) {
                $destination->setMacroRegion($aad['destination']['macroRegion']);
            }
            if (isset($aad['destination']['addressCountry'])) {
                $destination->setAddressCountry($aad['destination']['addressCountry']);
            }
            if (isset($aad['destination']['countryCode'])) {
                $destination->setCountryCode($aad['destination']['countryCode']);
            }
            if (isset($aad['destination']['latitude'])) {
                $destination->setLatitude($aad['destination']['latitude']);
            }
            if (isset($aad['destination']['longitude'])) {
                $destination->setLongitude($aad['destination']['longitude']);
            }
        } else {
            $destination = clone $origin;
        }

        $ad->setOutwardWaypoints([clone $origin, clone $destination]);
        $ad->setReturnWaypoints([clone $destination, clone $origin]);
        
        // role
        $ad->setRole(Ad::ROLE_PASSENGER);
        
        // we set the ad as a solidary ad
        $ad->setSolidary(true);

        // initialize to one way for now
        $ad->setOneWay(true);

        // frequency set to punctual for now
        $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        if ($aad['regular']) {
            if (!isset($aad['regularMinDate'])) {
                throw new SolidaryException(SolidaryException::REGULAR_MIN_DATE_REQUIRED);
            }
            if (!isset($aad['regularMaxDate'])) {
                throw new SolidaryException(SolidaryException::REGULAR_MAX_DATE_REQUIRED);
            }
            $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
            $ad->setOutwardDate(new DateTime($aad['regularMinDate']));
            $ad->setOutwardLimitDate(new DateTime($aad['regularMaxDate']));
            $ad->setSchedule($aad['regularSchedules']);
            // check if there are returns
            $return = false;
            foreach ($aad['regularSchedules'] as $schedule) {
                if ($schedule['returnTime']) {
                    $return = true;
                    break;
                }
            }
            if ($return) {
                $ad->setOneWay(false);
                $ad->setReturnDate(new DateTime($aad['regularMinDate']));
                $ad->setReturnLimitDate(new DateTime($aad['regularMaxDate']));
            }
        } else {
            $ad->setOutwardDate(new DateTime($aad['punctualOutwardMinDate']));
            $schedule = null;
            switch ($aad['punctualOutwardDateChoice']) {
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_DATE:
                    break;
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_7:
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_15:
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_30:
                    // transform to regular trip as the date is flexible
                    $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
                    $schedule = ['mon' => true, 'tue' => true, 'wed' => true, 'thu' => true, 'fri' => true, 'sat' => true, 'sun' => true];
                    $ad->setOutwardLimitDate(new DateTime($aad['punctualOutwardMaxDate']));
                    break;
            }
            switch ($aad['punctualOutwardTimeChoice']) {
                case Solidary::PUNCTUAL_TIME_CHOICE_TIME:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($aad['punctualOutwardMinTime']);
                    } else {
                        $schedule['outwardTime'] = $aad['punctualOutwardMinTime'];
                    }
                    break;
                case Solidary::PUNCTUAL_TIME_CHOICE_M:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($times['mTime']);
                    } else {
                        $schedule['outwardTime'] = $times['mTime'];
                    }
                    $ad->setMarginduration($times['mMargin']);
                    break;
                case Solidary::PUNCTUAL_TIME_CHOICE_A:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($times['aTime']);
                    } else {
                        $schedule['outwardTime'] = $times['aTime'];
                    }
                    $ad->setMarginduration($times['aMargin']);
                    break;
                case Solidary::PUNCTUAL_TIME_CHOICE_E:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($times['eTime']);
                    } else {
                        $schedule['outwardTime'] = $times['eTime'];
                    }
                    $ad->setMarginduration($times['eMargin']);
                    break;
            }
            switch ($aad['punctualReturnDateChoice']) {
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_NULL:
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_1:
                    // add 1 hour to outward time
                    $now = new DateTime();
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $now->setTime((int)substr($ad->getOutwardTime(), 0, 2), (int)substr($ad->getOutwardTime(), 2, 2));
                        $now->add(new DateInterval('PT1H'));
                        $ad->setReturnTime($now->format('H:i'));
                        $ad->setReturnDate($ad->getOutwardDate());
                    } else {
                        $now->setTime((int)substr($schedule['outwardTime'], 0, 2), (int)substr($schedule['outwardTime'], 2, 2));
                        $now->add(new DateInterval('PT1H'));
                        $schedule['returnTime'] = $now->format('H:i');
                    }
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_2:
                    // add 2 hours to outward time
                    $now = new DateTime();
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $now->setTime((int)substr($ad->getOutwardTime(), 0, 2), (int)substr($ad->getOutwardTime(), 2, 2));
                        $now->add(new DateInterval('PT2H'));
                        $ad->setReturnTime($now->format('H:i'));
                        $ad->setReturnDate($ad->getOutwardDate());
                    } else {
                        $now->setTime((int)substr($schedule['outwardTime'], 0, 2), (int)substr($schedule['outwardTime'], 2, 2));
                        $now->add(new DateInterval('PT2H'));
                        $schedule['returnTime'] = $now->format('H:i');
                    }
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_3:
                    // add 3 hours to outward time
                    $now = new DateTime();
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $now->setTime((int)substr($ad->getOutwardTime(), 0, 2), (int)substr($ad->getOutwardTime(), 2, 2));
                        $now->add(new DateInterval('PT3H'));
                        $ad->setReturnTime($now->format('H:i'));
                        $ad->setReturnDate($ad->getOutwardDate());
                    } else {
                        $now->setTime((int)substr($schedule['outwardTime'], 0, 2), (int)substr($schedule['outwardTime'], 2, 2));
                        $now->add(new DateInterval('PT3H'));
                        $schedule['returnTime'] = $now->format('H:i');
                    }
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_DATE:
                    // chosen return date and time => only punctual
                    $ad->setReturnDate(new DateTime($aad['punctualReturnDate']));
                    $ad->setReturnTime($aad['punctualReturnTime']);
                    break;
            }
            if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                $ad->setSchedule($schedule);
            }
        }


        // not a round-trip ??
        //$ad->setOneWay(true);


        // // We set the date and time of the demand
        // $ad->setOutwardDate($solidary->getOutwardDatetime());
        // $ad->setReturnDate($solidary->getReturnDatetime() ? $solidary->getReturnDatetime() : null);
        // $ad->setOutwardTime($solidary->getOutwardDatetime()->format("H:i"));
        // $ad->setReturnTime($solidary->getReturnDatetime() ? $solidary->getReturnDatetime()->format("H:i") : null);
        // if ($solidary->getFrequency() === criteria::FREQUENCY_REGULAR) {
        //     $ad->setFrequency(Criteria::FREQUENCY_REGULAR);

        //     // we set the schedule and the limit date of the regular demand
        //     $ad->setOutwardLimitDate($solidary->getOutwardDeadlineDatetime());
        //     $ad->setReturnLimitDate($solidary->getReturnDeadlineDatetime() ? $solidary->getReturnDeadlineDatetime() : null);

        //     $days = $solidary->getDays();
        //     // Check if there is a outward time for each given day
        //     $outwardTimes = $solidary->getOutwardTimes();
        //     if (is_null($outwardTimes)) {
        //         throw new SolidaryException(SolidaryException::NO_OUTWARD_TIMES);
        //     }
        //     foreach ($days as $outwardDay => $outwardDayChecked) {
        //         if (
        //             !array_key_exists($outwardDay, $outwardTimes) ||
        //             ((bool)$outwardDayChecked && is_null($outwardTimes[$outwardDay]))
        //         ) {
        //             throw new SolidaryException(SolidaryException::DAY_CHECK_BUT_NO_OUTWARD_TIME);
        //         }
        //     }

        //     if (!is_null($solidary->getReturnDatetime())) {
        //         $returnTimes = $solidary->getReturnTimes();
        //         if (is_null($returnTimes)) {
        //             throw new SolidaryException(SolidaryException::NO_RETURN_TIMES);
        //         }

        //         // Check if there is a return time for each given day
        //         foreach ($days as $returnDay => $returnDayChecked) {
        //             if (
        //                 !array_key_exists($returnDay, $returnTimes) ||
        //                 (true===$returnDayChecked && is_null($returnTimes[$returnDay]))
        //             ) {
        //                 throw new SolidaryException(SolidaryException::DAY_CHECK_BUT_NO_RETURN_TIME);
        //             }
        //         }
        //         $ad->setOneWay(false);
        //     }

        //     // We build the schedule
        //     $buildedSchedules = $this->buildSchedulesForAd($solidary->getDays(), $solidary->getOutwardTimes(), $solidary->getReturnTimes());

        //     $ad->setSchedule($buildedSchedules);
        // }
        // // we set the margin time of the demand
        // $ad->setMarginDuration($solidary->getMarginDuration() ? $solidary->getMarginDuration() : null);

        // // If the destination is not specified we use the origin
        // if ($destination == null) {
        //     $destination = $origin;
        // }
        // // Outward waypoint
        // $outwardWaypoints = [
        //     clone $origin,
        //     clone $destination
        // ];

        // $ad->setOutwardWaypoints($outwardWaypoints);

        // // return waypoint
        // $returnWaypoints = [
        //     clone $destination,
        //     clone $origin
        // ];

        // $ad->setReturnWaypoints($returnWaypoints);
        
        // // The User
        // $ad->setUserId($userId ? $userId : $solidary->getSolidaryUser()->getUser()->getId());

        // // The subject
        // $ad->setSubjectId($solidary->getSubject()->getId());

        return $this->adManager->createAd($ad);
    }

    private function getTimeAndMarginForStructure(Structure $structure)
    {
        $now = new Datetime();
        $mMinTime = clone $now;
        $mMaxTime = clone $now;
        $aMinTime = clone $now;
        $aMaxTime = clone $now;
        $eMinTime = clone $now;
        $eMaxTime = clone $now;
        $mMinTime->setTime($structure->getMMinTime()->format('H'), $structure->getMMinTime()->format('i'));
        $mMaxTime->setTime($structure->getMMaxTime()->format('H'), $structure->getMMaxTime()->format('i'));
        $aMinTime->setTime($structure->getAMinTime()->format('H'), $structure->getAMinTime()->format('i'));
        $aMaxTime->setTime($structure->getAMaxTime()->format('H'), $structure->getAMaxTime()->format('i'));
        $eMinTime->setTime($structure->getEMinTime()->format('H'), $structure->getEMinTime()->format('i'));
        $eMaxTime->setTime($structure->getEMaxTime()->format('H'), $structure->getEMaxTime()->format('i'));
        $mMargin = ($mMaxTime->getTimestamp() - $mMinTime->getTimestamp()) / 2;
        $aMargin = ($aMaxTime->getTimestamp() - $aMinTime->getTimestamp()) / 2;
        $eMargin = ($eMaxTime->getTimestamp() - $eMinTime->getTimestamp()) / 2;
        return [
            'mTime' => $mMinTime->add(new DateInterval('PT'.$mMargin.'S'))->format('H:i'),
            'aTime' => $aMinTime->add(new DateInterval('PT'.$aMargin.'S'))->format('H:i'),
            'eTime' => $eMinTime->add(new DateInterval('PT'.$eMargin.'S'))->format('H:i'),
            'mMargin' => $mMargin,
            'aMargin' => $aMargin,
            'eMargin' => $eMargin
        ];
    }
}
