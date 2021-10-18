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
use App\Solidary\Admin\Exception\SolidaryException;
use App\Solidary\Entity\SolidaryBeneficiary;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\Solidary;
use App\Action\Entity\Diary;
use App\Paginator\MobicoopPaginator;
use App\Service\FormatDataManager;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\Structure;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Admin\Event\BeneficiaryStatusChangedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DateTime;

/**
 * Solidary beneficiary manager in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryBeneficiaryManager
{
    private $entityManager;
    private $solidaryUserRepository;
    private $solidaryUserStructureRepository;
    private $formatDataManager;
    private $eventDispatcher;
    private $fileFolder;
    
    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SolidaryUserRepository $solidaryUserRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        FormatDataManager $formatDataManager,
        EventDispatcherInterface $eventDispatcher,
        string $fileFolder
    ) {
        $this->entityManager = $entityManager;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->formatDataManager = $formatDataManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileFolder = $fileFolder;
    }

    /**
     * Get Solidary Beneficiary records (transform SolidaryUsers to SolidaryBeneficiaries)
     *
     * @param PaginatorInterface $solidaryUsers  The solidary user objects
     * @return array|null The solidary beneficiary records
     */
    public function getSolidaryBeneficiaries(PaginatorInterface $solidaryUsers)
    {
        $solidaryBeneficiaries = [];
        foreach ($solidaryUsers as $solidaryUser) {
            /**
             * @var SolidaryUser $solidaryUser
             */
            $solidaryBeneficiaries[] = $this->createSolidaryBeneficiaryFromSolidaryUser($solidaryUser);
        }
        // we need to return a paginator, we already have all informations but we need to build a custom paginator object
        return new MobicoopPaginator($solidaryBeneficiaries, $solidaryUsers->getCurrentPage(), $solidaryUsers->getItemsPerPage(), $solidaryUsers->getTotalItems());
    }

    /**
     * Create a SolidaryBeneficiary from a SolidaryUser
     *
     * @param SolidaryUser $solidaryUser    The SolidaryUser
     * @param boolean $withDiary            Include the diary for the SolidaryUser
     * @param boolean $withProofs           Include the proofs for the SolidaryUser
     * @return SolidaryBeneficiary          The SolidaryBeneficiary
     */
    private function createSolidaryBeneficiaryFromSolidaryUser(SolidaryUser $solidaryUser, bool $withDiary = false, bool $withProofs = false): SolidaryBeneficiary
    {
        $solidaryBeneficiary = new SolidaryBeneficiary();
        $solidaryBeneficiary->setId($solidaryUser->getId());
        $solidaryBeneficiary->setUserId($solidaryUser->getUser()->getId());
        $solidaryBeneficiary->setEmail($solidaryUser->getUser()->getEmail());
        $solidaryBeneficiary->setTelephone($solidaryUser->getUser()->getTelephone());
        $solidaryBeneficiary->setGivenName($solidaryUser->getUser()->getGivenName());
        $solidaryBeneficiary->setFamilyName($solidaryUser->getUser()->getFamilyName());
        $solidaryBeneficiary->setGender($solidaryUser->getUser()->getGender());
        $solidaryBeneficiary->setBirthDate($solidaryUser->getUser()->getBirthDate());
        $solidaryBeneficiary->setNewsSubscription($solidaryUser->getUser()->hasNewsSubscription());
        $solidaryBeneficiary->setHomeAddress($solidaryUser->getUser()->getHomeAddress() ? $solidaryUser->getUser()->getHomeAddress()->jsonSerialize() : null);
        $solidaryBeneficiary->setAvatar($solidaryUser->getUser()->getAvatar());
        
        // get the status of the beneficiary for each structure attached, and get the diary and proofs if asked
        $beneficiaryStructures = [];
        $diaries = [];
        $proofs = [];
        foreach ($solidaryUser->getSolidaryUserStructures() as $solidaryUserStructure) {
            /**
             * @var SolidaryUserStructure $solidaryUserStructure
             */

            $beneficiaryStructures[] = [
                "id" => $solidaryUserStructure->getStructure()->getId(),
                "name" => $solidaryUserStructure->getStructure()->getName(),
                "status" => $solidaryUserStructure->getStatus()
            ];
            // get the diary
            if ($withDiary) {
                $diaries = $this->getDiaryForSolidaryUserStructure($solidaryUserStructure);
            }
            // get the proofs
            if ($withProofs) {
                $proofs = $this->getProofsForSolidaryUserStructure($solidaryUserStructure);
            }
        }
        $solidaryBeneficiary->setStructures($beneficiaryStructures);
        // reorder diaries by date and solidary id
        usort($diaries, function ($a, $b) {
            if ($a['date'] == $b['date']) {
                return $b['solidary'] <=> $a['solidary'];
            }
            return $b['date'] <=> $a['date'];
        });
        $solidaryBeneficiary->setDiaries($diaries);
        $solidaryBeneficiary->setProofs($proofs);

        return $solidaryBeneficiary;
    }

    /**
     * Get the diary entries for a solidaryUserStructure
     *
     * @param SolidaryUserStructure         $solidaryUserStructure  The solidaryUserStructure
     * @return array    The proofs
     */
    private function getDiaryForSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure)
    {
        $diaries = [];
        foreach ($solidaryUserStructure->getSolidaries() as $solidary) {
            /**
             * @var Solidary $solidary
             */
            foreach ($solidary->getDiaries() as $diary) {
                /**
                 * @var Diary $diary
                 */
                $diaries[] = [
                    'action' => $diary->getAction()->getName(),
                    'comment' => $diary->getComment(),
                    'progression' => $diary->getProgression(),
                    'authorGivenName' => $diary->getAuthor()->getGivenName(),
                    'authorFamilyName' => $diary->getAuthor()->getFamilyName(),
                    'authorAvatar' => $diary->getAuthor()->getAvatar(),
                    'userId' => $diary->getUser()->getId(),
                    'givenName' => $diary->getUser()->getGivenName(),
                    'familyName' => $diary->getUser()->getFamilyName(),
                    'avatar' => $diary->getUser()->getAvatar(),
                    'date' => $diary->getCreatedDate(),
                    'solidary' => $solidary->getId(),
                    'origin' => $solidary->getAdminorigin()->jsonSerialize(),
                    'destination' => $solidary->getAdmindestination()->jsonSerialize()
                ];
            }
        }
        return $diaries;
    }

    /**
     * Get the proofs for a solidaryUserStructure
     *
     * @param SolidaryUserStructure $solidaryUserStructure  The solidaryUserStructure
     * @param Structure|null        $structure              The structure if we want the proofs for a given structure only
     * @return array    The proofs
     */
    public function getProofsForSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure, ?Structure $structure = null)
    {
        $proofs = [];
        foreach ($solidaryUserStructure->getProofs() as $proof) {
            /**
             * @var Proof $proof
             */
            if (is_null($structure) || ($proof->getSolidaryUserStructure()->getStructure()->getId() === $structure->getId())) {
                // get the real value for checkbox, selectbox, radio
                $value = $proof->getValue();
                if ($proof->getStructureProof()->isCheckbox()) {
                    $value = (bool)$proof->getValue();
                } elseif ($proof->getStructureProof()->isRadio() || $proof->getStructureProof()->isSelectbox()) {
                    $options = explode(';', $proof->getStructureProof()->getOptions());
                    $values = explode(';', $proof->getStructureProof()->getAcceptedValues());
                    if ($key = array_search($proof->getValue(), $values)) {
                        $value = $options[$key];
                    }
                }
                $proofs[] = [
                    'proofId' => $proof->getId(),
                    'structure' => $solidaryUserStructure->getStructure()->getName(),
                    'structureId' => $solidaryUserStructure->getStructure()->getId(),
                    'structureProofId' => $proof->getStructureProof()->getId(),
                    'userStructureId' => $solidaryUserStructure->getId(),
                    'status' => $solidaryUserStructure->getStatus(),
                    'label' => $proof->getStructureProof()->getLabel(),
                    'checkbox' => $proof->getStructureProof()->isCheckbox(),
                    'input' => $proof->getStructureProof()->isInput(),
                    'selectbox' => $proof->getStructureProof()->isSelectbox(),
                    'radio' => $proof->getStructureProof()->isRadio(),
                    'file' => $proof->getStructureProof()->isFile(),
                    'value' => $value,
                    'originalName' => $proof->getStructureProof()->isFile() ? $proof->getOriginalName() : null,
                    'fileName' => $proof->getStructureProof()->isFile() ? $this->fileFolder.rawurlencode($proof->getFileName()) : null,
                    'fileSize' => $proof->getStructureProof()->isFile() ? $this->formatDataManager->convertFilesize($proof->getSize()) : null
                ];
            }
        }
        return $proofs;
    }

    /**
     * Get a Solidary Beneficiary by its id
     *
     * @param integer $id                   The Solidary Beneficiary id
     * @return SolidaryBeneficiary|null     The Solidary Beneficiary or null if not found
     */
    public function getSolidaryBeneficiary(int $id)
    {
        if (!$solidaryUser = $this->solidaryUserRepository->find($id)) {
            throw new SolidaryException(sprintf(SolidaryException::BENEFICIARY_NOT_FOUND, $id));
        }
        if (!$solidaryUser->isBeneficiary()) {
            throw new SolidaryException(sprintf(SolidaryException::BENEFICIARY_NOT_FOUND, $id));
        }
        return $this->createSolidaryBeneficiaryFromSolidaryUser($solidaryUser, true, true);
    }

    /**
     * Patch a solidary beneficiary.
     *
     * @param int   $id         The id of the solidaryBeneficiary to update
     * @param array $fields     The updated fields
     * @return SolidaryBeneficiary     The solidaryBeneficiary updated
     */
    public function patchSolidaryBeneficiary(int $id, array $fields)
    {
        if (!$solidaryUser = $this->solidaryUserRepository->find($id)) {
            throw new SolidaryException(sprintf(SolidaryException::BENEFICIARY_NOT_FOUND, $id));
        }
        
        // check if a new validation has been made
        if (array_key_exists('validation', $fields)) {
            return $this->treatValidation($solidaryUser, $fields['validation']);
        }

        // check if beneficiary informations have been updated
        if (isset($fields['givenName']) && $fields['givenName'] != $solidaryUser->getUser()->getGivenName()) {
            $solidaryUser->getUser()->setGivenName($fields['givenName']);
        }
        if (isset($fields['familyName']) && $fields['familyName'] != $solidaryUser->getUser()->getFamilyName()) {
            $solidaryUser->getUser()->setFamilyName($fields['familyName']);
        }
        if (isset($fields['email']) && $fields['email'] != $solidaryUser->getUser()->getEmail()) {
            $solidaryUser->getUser()->setEmail($fields['email']);
        }
        if (isset($fields['telephone']) && $fields['telephone'] != $solidaryUser->getUser()->getTelephone()) {
            $solidaryUser->getUser()->setTelephone($fields['telephone']);
        }
        if (isset($fields['gender']) && $fields['gender'] != $solidaryUser->getUser()->getGender()) {
            $solidaryUser->getUser()->setGender($fields['gender']);
        }
        if (isset($fields['birthDate']) && $fields['birthDate'] != $solidaryUser->getUser()->getBirthDate()) {
            $solidaryUser->getUser()->setBirthDate(new DateTime($fields['birthDate']));
        }
        if (isset($fields['newsSubscription']) && $fields['newsSubscription'] != $solidaryUser->getUser()->hasNewsSubscription()) {
            $solidaryUser->getUser()->setNewsSubscription($fields['newsSubscription']);
        }
        // check if beneficiary home address has been updated
        if (isset($fields['homeAddress'])) {
            // we search the original home address
            $homeAddress = null;
            foreach ($solidaryUser->getUser()->getAddresses() as $address) {
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
                if (isset($fields['homeAddress']['streetAddress']) && $homeAddress->getStreetAddress() != $fields['homeAddress']['streetAddress']) {
                    $updated = true;
                    $homeAddress->setStreetAddress($fields['homeAddress']['streetAddress']);
                }
                if (isset($fields['homeAddress']['postalCode']) && $homeAddress->getPostalCode() != $fields['homeAddress']['postalCode']) {
                    $updated = true;
                    $homeAddress->setPostalCode($fields['homeAddress']['postalCode']);
                }
                if (isset($fields['homeAddress']['addressLocality']) && $homeAddress->getAddressLocality() != $fields['homeAddress']['addressLocality']) {
                    $updated = true;
                    $homeAddress->setAddressLocality($fields['homeAddress']['addressLocality']);
                }
                if (isset($fields['homeAddress']['addressCountry']) && $homeAddress->getAddressCountry() != $fields['homeAddress']['addressCountry']) {
                    $updated = true;
                    $homeAddress->setAddressCountry($fields['homeAddress']['addressCountry']);
                }
                if (isset($fields['homeAddress']['latitude']) && $homeAddress->getLatitude() != $fields['homeAddress']['latitude']) {
                    $updated = true;
                    $homeAddress->setLatitude($fields['homeAddress']['latitude']);
                }
                if (isset($fields['homeAddress']['longitude']) && $homeAddress->getLongitude() != $fields['homeAddress']['longitude']) {
                    $updated = true;
                    $homeAddress->setLongitude($fields['homeAddress']['longitude']);
                }
                if (isset($fields['homeAddress']['houseNumber']) && $homeAddress->getHouseNumber() != $fields['homeAddress']['houseNumber']) {
                    $updated = true;
                    $homeAddress->setHouseNumber($fields['homeAddress']['houseNumber']);
                }
                if (isset($fields['homeAddress']['subLocality']) && $homeAddress->getSubLocality() != $fields['homeAddress']['subLocality']) {
                    $updated = true;
                    $homeAddress->setSubLocality($fields['homeAddress']['subLocality']);
                }
                if (isset($fields['homeAddress']['localAdmin']) && $homeAddress->getLocalAdmin() != $fields['homeAddress']['localAdmin']) {
                    $updated = true;
                    $homeAddress->setLocalAdmin($fields['homeAddress']['localAdmin']);
                }
                if (isset($fields['homeAddress']['county']) && $homeAddress->getCounty() != $fields['homeAddress']['county']) {
                    $updated = true;
                    $homeAddress->setCounty($fields['homeAddress']['county']);
                }
                if (isset($fields['homeAddress']['macroCounty']) && $homeAddress->getMacroCounty() != $fields['homeAddress']['macroCounty']) {
                    $updated = true;
                    $homeAddress->setMacroCounty($fields['homeAddress']['macroCounty']);
                }
                if (isset($fields['homeAddress']['region']) && $homeAddress->getRegion() != $fields['homeAddress']['region']) {
                    $updated = true;
                    $homeAddress->setRegion($fields['homeAddress']['region']);
                }
                if (isset($fields['homeAddress']['macroRegion']) && $homeAddress->getMacroRegion() != $fields['homeAddress']['macroRegion']) {
                    $updated = true;
                    $homeAddress->setMacroRegion($fields['homeAddress']['macroRegion']);
                }
                if (isset($fields['homeAddress']['countryCode']) && $homeAddress->getCountryCode() != $fields['homeAddress']['countryCode']) {
                    $updated = true;
                    $homeAddress->setCountryCode($fields['homeAddress']['countryCode']);
                }
                if ($updated) {
                    $this->entityManager->persist($homeAddress);
                }
            }
        }
        
        // persist the solidary beneficiary
        $this->entityManager->persist($solidaryUser->getUser());
        $this->entityManager->flush();

        return $this->getSolidaryBeneficiary($solidaryUser->getId());
    }

    /**
     * Treat a validation for a solidary beneficiary.
     *
     * @param SolidaryUser          $solidaryUser           The solidaryUser corresponding to the solidarybeneficiary to update
     * @param array                 $validation             The validation fields
     * @return SolidaryBeneficiary  The solidaryBeneficiary updated
     */
    private function treatValidation(SolidaryUser $solidaryUser, array $validation)
    {
        if (!array_key_exists('validate', $validation)) {
            throw new SolidaryException(SolidaryException::BENEFICIARY_VALIDATION_VALUE_REQUIRED);
        }
        if (!array_key_exists('id', $validation)) {
            throw new SolidaryException(SolidaryException::BENEFICIARY_VALIDATION_ID_REQUIRED);
        }
        if (!$solidaryUserStructure = $this->solidaryUserStructureRepository->find($validation['id'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_USER_STRUCTURE_NOT_FOUND, $validation['id']));
        }

        $solidaryUserStructure->setStatus($validation['validate'] === true ? SolidaryUserStructure::STATUS_ACCEPTED : SolidaryUserStructure::STATUS_REFUSED);
        $this->entityManager->persist($solidaryUserStructure);
        $this->entityManager->flush();

        // dispatch the event
        $event = new BeneficiaryStatusChangedEvent($solidaryUserStructure);
        $this->eventDispatcher->dispatch($event, BeneficiaryStatusChangedEvent::NAME);

        return $this->getSolidaryBeneficiary($solidaryUser->getId());
    }
}
