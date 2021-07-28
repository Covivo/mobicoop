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
use App\Paginator\MobicoopPaginator;
use App\Service\FormatDataManager;
use App\Solidary\Admin\Exception\SolidaryException;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Admin\Event\VolunteerStatusChangedEvent;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Solidary volunteer manager in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryVolunteerManager
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
     * Get Solidary Volunteer records (transform SolidaryUsers to SolidaryVolunteers)
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
            $solidaryVolunteers[] = $this->createSolidaryVolunteerFromSolidaryUser($solidaryUser);
        }
        // we need to return a paginator, we already have all informations but we need to build a custom paginator object
        return new MobicoopPaginator($solidaryVolunteers, $solidaryUsers->getCurrentPage(), $solidaryUsers->getItemsPerPage(), $solidaryUsers->getTotalItems());
    }

    /**
     * Create a SolidaryVolunteer from a SolidaryUser
     *
     * @param SolidaryUser $solidaryUser    The SolidaryUser
     * @param boolean $withDiary            Include the diary for the SolidaryUser
     * @param boolean $withProofs           Include the proofs for the SolidaryUser
     * @return SolidaryVolunteer            The SolidaryVolunteer
     */
    private function createSolidaryVolunteerFromSolidaryUser(SolidaryUser $solidaryUser, bool $withDiary = false, bool $withProofs = false): SolidaryVolunteer
    {
        $solidaryVolunteer = new SolidaryVolunteer();
        $solidaryVolunteer->setId($solidaryUser->getId());
        $solidaryVolunteer->setUserId($solidaryUser->getUser()->getId());
        $solidaryVolunteer->setEmail($solidaryUser->getUser()->getEmail());
        $solidaryVolunteer->setTelephone($solidaryUser->getUser()->getTelephone());
        $solidaryVolunteer->setGivenName($solidaryUser->getUser()->getGivenName());
        $solidaryVolunteer->setFamilyName($solidaryUser->getUser()->getFamilyName());
        $solidaryVolunteer->setGender($solidaryUser->getUser()->getGender());
        $solidaryVolunteer->setBirthDate($solidaryUser->getUser()->getBirthDate());
        $solidaryVolunteer->setNewsSubscription($solidaryUser->getUser()->hasNewsSubscription());
        $solidaryVolunteer->setHomeAddress($solidaryUser->getUser()->getHomeAddress() ? $solidaryUser->getUser()->getHomeAddress()->jsonSerialize() : null);
        $solidaryVolunteer->setAvatar($solidaryUser->getUser()->getAvatar());
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
        $proofs = [];
        foreach ($solidaryUser->getSolidaryUserStructures() as $solidaryUserStructure) {
            /**
             * @var SolidaryUserStructure $solidaryUserStructure
             */

            $volunteerStructures[] = [
                "id" => $solidaryUserStructure->getStructure()->getId(),
                "name" => $solidaryUserStructure->getStructure()->getName(),
                "status" => $solidaryUserStructure->getStatus()
            ];
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
        $solidaryVolunteer->setStructures($volunteerStructures);
        $solidaryVolunteer->setProofs($proofs);

        return $solidaryVolunteer;
    }

    /**
     * Get a Solidary Volunteer by its id
     *
     * @param integer $id                   The Solidary Volunteer id
     * @return SolidaryVolunteer|null       The Solidary Volunteer or null if not found
     */
    public function getSolidaryVolunteer(int $id)
    {
        if (!$solidaryUser = $this->solidaryUserRepository->find($id)) {
            throw new SolidaryException(sprintf(SolidaryException::VOLUNTEER_NOT_FOUND, $id));
        }
        if (!$solidaryUser->isVolunteer()) {
            throw new SolidaryException(sprintf(SolidaryException::VOLUNTEER_NOT_FOUND, $id));
        }
        return $this->createSolidaryVolunteerFromSolidaryUser($solidaryUser, true, true);
    }

    /**
     * Patch a solidary volunteer.
     *
     * @param int   $id         The id of the solidaryVolunteer to update
     * @param array $fields     The updated fields
     * @return SolidaryVolunteer     The solidaryVolunteer updated
     */
    public function patchSolidaryVolunteer(int $id, array $fields)
    {
        if (!$solidaryUser = $this->solidaryUserRepository->find($id)) {
            throw new SolidaryException(sprintf(SolidaryException::VOLUNTEER_NOT_FOUND, $id));
        }
        
        // check if a new validation has been made
        if (array_key_exists('validation', $fields)) {
            return $this->treatValidation($solidaryUser, $fields['validation']);
        }

        // persist the solidary volunteer
        $this->entityManager->persist($solidaryUser->getUser());
        $this->entityManager->flush();

        return $this->getSolidaryVolunteer($solidaryUser->getId());
    }

    /**
     * Treat a validation for a solidary volunteer.
     *
     * @param SolidaryUser          $solidaryUser           The solidaryUser corresponding to the solidaryVolunteer to update
     * @param array                 $validation             The validation fields
     * @return SolidaryVolunteer  The solidaryVolunteer updated
     */
    private function treatValidation(SolidaryUser $solidaryUser, array $validation)
    {
        if (!array_key_exists('validate', $validation)) {
            throw new SolidaryException(SolidaryException::VOLUNTEER_VALIDATION_VALUE_REQUIRED);
        }
        if (!array_key_exists('id', $validation)) {
            throw new SolidaryException(SolidaryException::VOLUNTEER_VALIDATION_ID_REQUIRED);
        }
        if (!$solidaryUserStructure = $this->solidaryUserStructureRepository->find($validation['id'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_USER_STRUCTURE_NOT_FOUND, $validation['id']));
        }

        $solidaryUserStructure->setStatus($validation['validate'] === true ? SolidaryUserStructure::STATUS_ACCEPTED : SolidaryUserStructure::STATUS_REFUSED);
        $this->entityManager->persist($solidaryUserStructure);
        $this->entityManager->flush();

        // dispatch the event
        $event = new VolunteerStatusChangedEvent($solidaryUserStructure);
        $this->eventDispatcher->dispatch($event, VolunteerStatusChangedEvent::NAME);

        return $this->getSolidaryVolunteer($solidaryUser->getId());
    }
}
