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
 */

namespace App\Solidary\Admin\Service;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Auth\Entity\AuthItem;
use App\Auth\ServiceAdmin\AuthManager;
use App\Paginator\MobicoopPaginator;
use App\Service\FormatDataManager;
use App\Solidary\Admin\Event\VolunteerStatusChangedEvent;
use App\Solidary\Admin\Exception\SolidaryException;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private $authManager;
    private $formatDataManager;
    private $eventDispatcher;
    private $fileFolder;

    /**
     * @var SolidaryUser
     */
    private $solidaryUser;

    /**
     * @var array The fields that we are trying to update
     */
    private $fields;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SolidaryUserRepository $solidaryUserRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        AuthManager $authManager,
        FormatDataManager $formatDataManager,
        EventDispatcherInterface $eventDispatcher,
        string $fileFolder
    ) {
        $this->entityManager = $entityManager;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->authManager = $authManager;
        $this->formatDataManager = $formatDataManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileFolder = $fileFolder;
    }

    /**
     * Get Solidary Volunteer records (transform SolidaryUsers to SolidaryVolunteers).
     *
     * @param PaginatorInterface $solidaryUsers The solidary user objects
     *
     * @return null|array The solidary volunteer records
     */
    public function getSolidaryVolunteers(PaginatorInterface $solidaryUsers)
    {
        $solidaryVolunteers = [];
        foreach ($solidaryUsers as $solidaryUser) {
            // @var SolidaryUser $solidaryUser
            $solidaryVolunteers[] = $this->createSolidaryVolunteerFromSolidaryUser($solidaryUser);
        }
        // we need to return a paginator, we already have all informations but we need to build a custom paginator object
        return new MobicoopPaginator($solidaryVolunteers, $solidaryUsers->getCurrentPage(), $solidaryUsers->getItemsPerPage(), $solidaryUsers->getTotalItems());
    }

    /**
     * Get a Solidary Volunteer by its id.
     *
     * @param int $id The Solidary Volunteer id
     *
     * @return null|SolidaryVolunteer The Solidary Volunteer or null if not found
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
     * @param int   $id     The id of the solidaryVolunteer to update
     * @param array $fields The updated fields
     *
     * @return SolidaryVolunteer The solidaryVolunteer updated
     */
    public function patchSolidaryVolunteer(int $id, array $fields)
    {
        if (!$solidaryUser = $this->solidaryUserRepository->find($id)) {
            throw new SolidaryException(sprintf(SolidaryException::VOLUNTEER_NOT_FOUND, $id));
        }

        $this->setSolidaryUser($solidaryUser);
        $this->setFields($fields);

        $this->updateAvailabilities();
        $this->treatValidation();

        // persist the solidary volunteer
        $this->entityManager->persist($this->getSolidaryUser()->getUser());
        $this->entityManager->flush();

        return $this->getSolidaryVolunteer($this->getSolidaryUser()->getId());
    }

    public function checkIsVolunteer(SolidaryUserStructure $solidaryUserStructure)
    {
        $isVolunteer = false;

        foreach ($solidaryUserStructure->getSolidaryUser()->getSolidaryUserStructures() as $curSolidaryUserStructure) {
            if (SolidaryUserStructure::STATUS_ACCEPTED === $curSolidaryUserStructure->getStatus()) {
                $isVolunteer = true;

                break;
            }
        }
        if ($isVolunteer) {
            if ($authItem = $this->authManager->getAuthItem(AuthItem::ROLE_SOLIDARY_VOLUNTEER)) {
                $this->authManager->grant($solidaryUserStructure->getSolidaryUser()->getUser(), $authItem);
            }
            if ($authItem = $this->authManager->getAuthItem(AuthItem::ROLE_SOLIDARY_VOLUNTEER_CANDIDATE)) {
                $this->authManager->revoke($solidaryUserStructure->getSolidaryUser()->getUser(), $authItem, null);
            }
        } else {
            if ($authItem = $this->authManager->getAuthItem(AuthItem::ROLE_SOLIDARY_VOLUNTEER)) {
                $this->authManager->revoke($solidaryUserStructure->getSolidaryUser()->getUser(), $authItem, null);
            }
        }
    }

    private function getSolidaryUser(): SolidaryUser
    {
        return $this->solidaryUser;
    }

    private function setSolidaryUser(SolidaryUser $solidaryUser)
    {
        $this->solidaryUser = $solidaryUser;
    }

    private function getFields(): array
    {
        return $this->fields;
    }

    private function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Create a SolidaryVolunteer from a SolidaryUser.
     *
     * @param SolidaryUser $solidaryUser The SolidaryUser
     * @param bool         $withDiary    Include the diary for the SolidaryUser
     * @param bool         $withProofs   Include the proofs for the SolidaryUser
     *
     * @return SolidaryVolunteer The SolidaryVolunteer
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
            // @var SolidaryUserStructure $solidaryUserStructure

            $volunteerStructures[] = [
                'id' => $solidaryUserStructure->getStructure()->getId(),
                'userStructureId' => $solidaryUserStructure->getId(),
                'name' => $solidaryUserStructure->getStructure()->getName(),
                'status' => $solidaryUserStructure->getStatus(),
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
                        $value = (bool) $proof->getValue();
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
                        'fileSize' => $proof->getStructureProof()->isFile() ? $this->formatDataManager->convertFilesize($proof->getSize()) : null,
                    ];
                }
            }
        }
        $solidaryVolunteer->setStructures($volunteerStructures);
        $solidaryVolunteer->setProofs($proofs);

        return $solidaryVolunteer;
    }

    private function updateAvailabilities()
    {
        $this->updateDaysAvailabilities();
        $this->updateTimesRangeAvailabilities();
    }

    private function updateDaysAvailabilities()
    {
        foreach (SolidaryVolunteer::DAYS_SLOTS as $slot) {
            if (array_key_exists($slot, $this->getFields())) {
                $setter = 'set'.ucfirst($slot);
                if (method_exists($this->getSolidaryUser(), $setter)) {
                    $this->getSolidaryUser()->{$setter}($this->getFields()[$slot]);
                }
            }
        }
    }

    private function updateTimesRangeAvailabilities()
    {
        foreach (SolidaryVolunteer::TIMES_SLOTS as $slot) {
            if (array_key_exists($slot, $this->getFields())) {
                $setter = 'set'.ucfirst($slot);
                if (method_exists($this->getSolidaryUser(), $setter)) {
                    $datetime = new \DateTime($this->getFields()[$slot]);
                    if (!$datetime) {
                        throw new \LogicException('Datetime invalid');
                    }
                    $this->getSolidaryUser()->{$setter}($datetime);
                }
            }
        }
    }

    private function treatValidation()
    {
        if (!array_key_exists('validation', $this->getFields())) {
            return;
        }

        if (!array_key_exists('validate', $this->getFields()['validation'])) {
            throw new SolidaryException(SolidaryException::VOLUNTEER_VALIDATION_VALUE_REQUIRED);
        }
        if (!array_key_exists('id', $this->getFields()['validation'])) {
            throw new SolidaryException(SolidaryException::VOLUNTEER_VALIDATION_ID_REQUIRED);
        }
        if (!$solidaryUserStructure = $this->solidaryUserStructureRepository->find($this->getFields()['validation']['id'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_USER_STRUCTURE_NOT_FOUND, $this->getFields()['validation']['id']));
        }

        $solidaryUserStructure->setStatus(true === $this->getFields()['validation']['validate'] ? SolidaryUserStructure::STATUS_ACCEPTED : SolidaryUserStructure::STATUS_REFUSED);
        $this->entityManager->persist($solidaryUserStructure);
        $this->entityManager->flush();

        // dispatch the event
        $event = new VolunteerStatusChangedEvent($solidaryUserStructure);
        $this->eventDispatcher->dispatch($event, VolunteerStatusChangedEvent::NAME);
    }
}
