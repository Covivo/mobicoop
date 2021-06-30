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
use App\Service\FormatDataManager;
use App\Solidary\Entity\Proof;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;

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
        string $fileFolder
    ) {
        $this->entityManager = $entityManager;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->formatDataManager = $formatDataManager;
        $this->fileFolder = $fileFolder;
    }

    /**
     * Get Solidary Beneficiary records
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
        return $solidaryBeneficiaries;
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
        $solidaryBeneficiary->setHomeAddress($solidaryUser->getUser()->getHomeAddress()->jsonSerialize());
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
            }
            // get the proofs
            if ($withProofs) {
                foreach ($solidaryUserStructure->getProofs() as $proof) {
                    /**
                     * @var Proof $proof
                     */
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
                        'structure' => $solidaryUserStructure->getStructure()->getName(),
                        'structureId' => $solidaryUserStructure->getStructure()->getId(),
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
        }
        $solidaryBeneficiary->setStructures($beneficiaryStructures);
        // reorder diaries
        usort($diaries, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });
        $solidaryBeneficiary->setDiaries($diaries);
        $solidaryBeneficiary->setProofs($proofs);

        return $solidaryBeneficiary;
    }

    /**
     * Get a Solidary Beneficiary by its id
     *
     * @param integer $id                   The Solidary Beneficiary id
     * @return SolidaryBeneficiary|null     The Solidary Beneficiary or null if not found
     */
    public function getSolidaryBeneficiary(int $id)
    {
        if ($solidaryUser = $this->solidaryUserRepository->find($id)) {
            return $this->createSolidaryBeneficiaryFromSolidaryUser($solidaryUser, true, true);
        }
        throw new SolidaryException(sprintf(SolidaryException::BENEFICIARY_NOT_FOUND, $id));
    }

    /**
     * Patch a solidary beneficiary.
     *
     * @param SolidaryBeneficiary      $solidaryBeneficiary               The solidaryBeneficiary to update
     * @param array         $fields                 The updated fields
     * @return SolidaSolidaryBeneficiaryry     The solidaryBeneficiary updated
     */
    public function patchSolidaryBeneficiary(SolidaryBeneficiary $solidaryBeneficiary, array $fields)
    {
        // check if a new validation has been made
        if (array_key_exists('validation', $fields)) {
            return $this->treatValidation($solidaryBeneficiary, $fields['validation']);
        }
        
        // persist the solidary beneficiary
        $this->entityManager->persist($solidaryBeneficiary);
        $this->entityManager->flush();

        return $solidaryBeneficiary;
    }

    /**
     * Treat a validation for a solidary beneficiary.
     *
     * @param SolidaryBeneficiary   $solidaryBeneficiary    The solidaryBeneficiary to update
     * @param array                 $validation             The validation fields
     * @return SolidaryBeneficiary  The solidaryBeneficiary updated
     */
    private function treatValidation(SolidaryBeneficiary $solidaryBeneficiary, array $validation)
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

        return $this->getSolidaryBeneficiary($solidaryBeneficiary->getId());
    }
}
