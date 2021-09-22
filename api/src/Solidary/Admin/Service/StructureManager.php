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

use App\Auth\Entity\AuthItem;
use App\Auth\ServiceAdmin\AuthManager;
use App\Geography\Repository\TerritoryRepository;
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Operate;
use App\Solidary\Entity\Structure;
use App\Solidary\Entity\Subject;
use App\Solidary\Entity\StructureProof;
use App\Solidary\Exception\SolidaryException;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Structure manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class StructureManager
{
    private $entityManager;
    private $territoryRepository;
    private $userRepository;
    private $authManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TerritoryRepository $territoryRepository,
        UserRepository $userRepository,
        AuthManager $authManager
    ) {
        $this->entityManager = $entityManager;
        $this->territoryRepository = $territoryRepository;
        $this->userRepository = $userRepository;
        $this->authManager = $authManager;
    }

    /**
     * Add a structure.
     *
     * @param Structure     $structure              The structure to add
     * @param array         $fields                 The fields
     * @return Structure    The structure created
     */
    public function addStructure(Structure $structure, array $fields)
    {
        // treat territories
        if (in_array('territories', array_keys($fields))) {
            foreach ($fields["territories"] as $id) {
                if ($territory = $this->territoryRepository->find($id)) {
                    $structure->addTerritory($territory);
                } else {
                    throw new SolidaryException(SolidaryException::TERRITORY_INVALID);
                }
            }
        }

        // treat subjects
        if (in_array('subjects', array_keys($fields))) {
            foreach ($fields["subjects"] as $asubject) {
                if (array_key_exists('label', $asubject) && $asubject['label'] !== null) {
                    $subject = new Subject();
                    $subject->setLabel($asubject['label']);
                    $subject->setStructure($structure);
                    $structure->addSubject($subject);
                    $this->entityManager->persist($subject);
                }
            }
        }

        // treat needs
        if (in_array('needs', array_keys($fields))) {
            foreach ($fields["needs"] as $aneed) {
                if (array_key_exists('label', $aneed) && $aneed['label'] !== null) {
                    $need = new Need();
                    $need->setLabel($aneed['label']);
                    $need->setLabelVolunteer(isset($aneed['labelVolunteer']) ? $aneed['labelVolunteer'] : null);
                    $need->addStructure($structure);
                    $structure->addNeed($need);
                    $this->entityManager->persist($need);
                }
            }
        }

        // treat proofs
        if (in_array('structureProofs', array_keys($fields))) {
            foreach ($fields["structureProofs"] as $aproof) {
                if (array_key_exists('label', $aproof) && $aproof['label'] !== null) {
                    $proof = new StructureProof();
                    $proof->setLabel($aproof['label']);
                    $proof->setType($aproof['type']);
                    $proof->setMandatory(isset($aproof['mandatory']) && $aproof['mandatory'] ? true : false);
                    $proof->setPosition($aproof['position']);
                    $proof->setStructure($structure);
                    $proof->setCheckbox(false);
                    $proof->setRadio(false);
                    $proof->setInput(false);
                    $proof->setFile(false);
                    $proof->setSelectbox(false);
                    $proof->setOptions(null);
                    $proof->setAcceptedValues(null);
                    if (isset($aproof['checkbox']) && $aproof['checkbox']) {
                        $proof->setCheckbox(true);
                    } elseif (isset($aproof['radio']) && $aproof['radio']) {
                        $proof->setRadio(true);
                        $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                        $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                    } elseif (isset($aproof['input']) && $aproof['input']) {
                        $proof->setInput(true);
                    } elseif (isset($aproof['file']) && $aproof['file']) {
                        $proof->setFile(true);
                    } elseif (isset($aproof['selectbox']) && $aproof['selectbox']) {
                        $proof->setSelectbox(true);
                        $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                        $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                    }
                    $structure->addStructureProof($proof);
                    $this->entityManager->persist($proof);
                }
            }
        }

        // treat operators
        if (in_array('operators', array_keys($fields))) {
            foreach ($fields["operators"] as $operator) {
                if ($user = $this->userRepository->find($operator['id'])) {
                    $operate = new Operate();
                    $operate->setUser($user);
                    $structure->addOperate($operate);
                    if ($authItem = $this->authManager->getAuthItem(AuthItem::ROLE_SOLIDARY_MANAGER)) {
                        $this->authManager->grant($user, $authItem, null, false);
                    }
                } else {
                    throw new SolidaryException(SolidaryException::UNKNOWN_USER);
                }
            }
        }

        // reorder proofs (order may have changed during the persists)
        $proofs = $structure->getStructureProofs();
        $structure->removeStructureProofs();
        usort($proofs, [$this,"comparePosition"]);
        foreach ($proofs as $proof) {
            $structure->addStructureProof($proof);
        }

        // persist the structure
        $this->entityManager->persist($structure);
        $this->entityManager->flush();

        return $structure;
    }

    /**
     * Patch a structure.
     *
     * @param Structure $structure    The structure to update
     * @param array     $fields       The updated fields
     * @return Structure   The structure updated
     */
    public function patchStructure(Structure $structure, array $fields)
    {
        // check if territories have changed
        if (in_array('territories', array_keys($fields))) {
            
            // check if a territory has been removed
            foreach ($structure->getTerritories() as $territory) {
                if (!in_array($territory->getId(), $fields['territories'])) {
                    // territory removed
                    $structure->removeTerritory($territory);
                }
            }

            // check if a territory has been added
            $ids=[];
            foreach ($structure->getTerritories() as $territory) {
                $ids[] = $territory->getId();
            }
            foreach ($fields["territories"] as $id) {
                if (!in_array($id, $ids)) {
                    // territory added, check if the territory exists
                    if ($territory = $this->territoryRepository->find($id)) {
                        $structure->addTerritory($territory);
                    } else {
                        throw new SolidaryException(SolidaryException::TERRITORY_INVALID);
                    }
                }
            }
        }

        // check if subjects have changed
        if (in_array('subjects', array_keys($fields))) {
            
            // check if a subject has been removed
            $ids = [];
            foreach ($fields['subjects'] as $value) {
                if (array_key_exists('id', $value)) {
                    $ids[] = $value['id'];
                }
            }
            foreach ($structure->getSubjects() as $subject) {
                if (!in_array($subject->getId(), $ids)) {
                    // subject removed
                    $structure->removeSubject($subject);
                }
            }

            foreach ($fields["subjects"] as $asubject) {
                if (
                    array_key_exists('id', $asubject) &&
                    $asubject['id'] !== null &&
                    array_key_exists('label', $asubject) &&
                    $asubject['label'] !== null
                    ) {
                    // existing subject => update
                    foreach ($structure->getSubjects() as $subject) {
                        if ($subject->getId() === $asubject['id']) {
                            $subject->setLabel($asubject['label']);
                            break;
                        }
                    }
                } elseif (
                    !array_key_exists('id', $asubject) &&
                    array_key_exists('label', $asubject) &&
                    $asubject['label'] !== null
                    ) {
                    // new subject
                    $subject = new Subject();
                    $subject->setLabel($asubject['label']);
                    $subject->setStructure($structure);
                    $structure->addSubject($subject);
                    $this->entityManager->persist($subject);
                }
            }
        }

        // check if needs have changed
        if (in_array('needs', array_keys($fields))) {
            
            // check if a need has been removed
            $ids = [];
            foreach ($fields['needs'] as $value) {
                if (array_key_exists('id', $value)) {
                    $ids[] = $value['id'];
                }
            }
            foreach ($structure->getNeeds() as $need) {
                if (!in_array($need->getId(), $ids)) {
                    // need removed
                    $structure->removeNeed($need);
                }
            }

            foreach ($fields["needs"] as $aneed) {
                if (
                    array_key_exists('id', $aneed) &&
                    $aneed['id'] !== null &&
                    array_key_exists('label', $aneed) &&
                    $aneed['label'] !== null
                    ) {
                    // existing need => update
                    foreach ($structure->getNeeds() as $need) {
                        if ($need->getId() === $aneed['id']) {
                            $need->setLabel($aneed['label']);
                            $need->setLabelVolunteer(isset($aneed['labelVolunteer']) ? $aneed['labelVolunteer'] : null);
                            break;
                        }
                    }
                } elseif (
                    !array_key_exists('id', $aneed) &&
                    array_key_exists('label', $aneed) &&
                    $aneed['label'] !== null
                    ) {
                    // new need
                    $need = new Need();
                    $need->setLabel($aneed['label']);
                    $need->setLabelVolunteer(isset($aneed['labelVolunteer']) ? $aneed['labelVolunteer'] : null);
                    $need->addStructure($structure);
                    $structure->addNeed($need);
                    $this->entityManager->persist($need);
                }
            }
        }

        // check if beneficiary proofs have changed
        if (in_array('structureProofsRequester', array_keys($fields))) {
            $ids = [];
            foreach ($fields['structureProofsRequester'] as $proof) {
                if (array_key_exists('id', $proof)) {
                    $ids[] = $proof['id'];
                }
            }
            // check if a proof have been removed
            foreach ($structure->getStructureProofs() as $proof) {
                if ($proof->getType() == StructureProof::TYPE_REQUESTER && !in_array($proof->getId(), $ids)) {
                    // proof removed
                    $structure->removeStructureProof($proof);
                }
            }

            foreach ($fields["structureProofsRequester"] as $aproof) {
                if (
                    array_key_exists('id', $aproof) &&
                    $aproof['id'] !== null &&
                    array_key_exists('label', $aproof) &&
                    $aproof['label'] !== null
                    ) {
                    // existing proof => update
                    foreach ($structure->getStructureProofs() as $proof) {
                        /**
                         * @var StructureProof $proof
                         */
                        if ($proof->getId() === $aproof['id']) {
                            $proof->setLabel($aproof['label']);
                            $proof->setType($aproof['type']);
                            $proof->setMandatory(isset($aproof['mandatory']) && $aproof['mandatory'] ? true : false);
                            $proof->setPosition($aproof['position']);
                            $proof->setCheckbox(false);
                            $proof->setRadio(false);
                            $proof->setInput(false);
                            $proof->setFile(false);
                            $proof->setSelectbox(false);
                            $proof->setOptions(null);
                            $proof->setAcceptedValues(null);
                            if (isset($aproof['checkbox']) && $aproof['checkbox']) {
                                $proof->setCheckbox(true);
                            } elseif (isset($aproof['radio']) && $aproof['radio']) {
                                $proof->setRadio(true);
                                $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                                $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                            } elseif (isset($aproof['input']) && $aproof['input']) {
                                $proof->setInput(true);
                            } elseif (isset($aproof['file']) && $aproof['file']) {
                                $proof->setFile(true);
                            } elseif (isset($aproof['selectbox']) && $aproof['selectbox']) {
                                $proof->setSelectbox(true);
                                $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                                $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                            }
                            break;
                        }
                    }
                } elseif (
                    !array_key_exists('id', $aproof) &&
                    array_key_exists('label', $aproof) &&
                 $aproof['label'] !== null
                    ) {
                    // new proof
                    $proof = new StructureProof();
                    $proof->setLabel($aproof['label']);
                    $proof->setType($aproof['type']);
                    $proof->setMandatory(isset($aproof['mandatory']) && $aproof['mandatory'] ? true : false);
                    $proof->setPosition($aproof['position']);
                    $proof->setStructure($structure);
                    $proof->setCheckbox(false);
                    $proof->setRadio(false);
                    $proof->setInput(false);
                    $proof->setFile(false);
                    $proof->setSelectbox(false);
                    $proof->setOptions(null);
                    $proof->setAcceptedValues(null);
                    if (isset($aproof['checkbox']) && $aproof['checkbox']) {
                        $proof->setCheckbox(true);
                    } elseif (isset($aproof['radio']) && $aproof['radio']) {
                        $proof->setRadio(true);
                        $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                        $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                    } elseif (isset($aproof['input']) && $aproof['input']) {
                        $proof->setInput(true);
                    } elseif (isset($aproof['file']) && $aproof['file']) {
                        $proof->setFile(true);
                    } elseif (isset($aproof['selectbox']) && $aproof['selectbox']) {
                        $proof->setSelectbox(true);
                        $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                        $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                    }
                    $structure->addStructureProof($proof);
                    $this->entityManager->persist($proof);
                }
            }
        }

        // check if volunteer proofs have changed
        if (in_array('structureProofsVolunteer', array_keys($fields))) {
            $ids = [];
            foreach ($fields['structureProofsVolunteer'] as $proof) {
                if (array_key_exists('id', $proof)) {
                    $ids[] = $proof['id'];
                }
            }
            // check if a proof have been removed
            foreach ($structure->getStructureProofs() as $proof) {
                if ($proof->getType() == StructureProof::TYPE_VOLUNTEER && !in_array($proof->getId(), $ids)) {
                    // proof removed
                    $structure->removeStructureProof($proof);
                }
            }

            foreach ($fields["structureProofsVolunteer"] as $aproof) {
                if (
                    array_key_exists('id', $aproof) &&
                    $aproof['id'] !== null &&
                    array_key_exists('label', $aproof) &&
                    $aproof['label'] !== null
                    ) {
                    // existing proof => update
                    foreach ($structure->getStructureProofs() as $proof) {
                        /**
                         * @var StructureProof $proof
                         */
                        if ($proof->getId() === $aproof['id']) {
                            $proof->setLabel($aproof['label']);
                            $proof->setType($aproof['type']);
                            $proof->setMandatory(isset($aproof['mandatory']) && $aproof['mandatory'] ? true : false);
                            $proof->setPosition($aproof['position']);
                            $proof->setCheckbox(false);
                            $proof->setRadio(false);
                            $proof->setInput(false);
                            $proof->setFile(false);
                            $proof->setSelectbox(false);
                            $proof->setOptions(null);
                            $proof->setAcceptedValues(null);
                            if (isset($aproof['checkbox']) && $aproof['checkbox']) {
                                $proof->setCheckbox(true);
                            } elseif (isset($aproof['radio']) && $aproof['radio']) {
                                $proof->setRadio(true);
                                $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                                $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                            } elseif (isset($aproof['input']) && $aproof['input']) {
                                $proof->setInput(true);
                            } elseif (isset($aproof['file']) && $aproof['file']) {
                                $proof->setFile(true);
                            } elseif (isset($aproof['selectbox']) && $aproof['selectbox']) {
                                $proof->setSelectbox(true);
                                $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                                $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                            }
                            break;
                        }
                    }
                } elseif (
                    !array_key_exists('id', $aproof) &&
                    array_key_exists('label', $aproof) &&
                 $aproof['label'] !== null
                    ) {
                    // new proof
                    $proof = new StructureProof();
                    $proof->setLabel($aproof['label']);
                    $proof->setType($aproof['type']);
                    $proof->setMandatory(isset($aproof['mandatory']) && $aproof['mandatory'] ? true : false);
                    $proof->setPosition($aproof['position']);
                    $proof->setStructure($structure);
                    $proof->setCheckbox(false);
                    $proof->setRadio(false);
                    $proof->setInput(false);
                    $proof->setFile(false);
                    $proof->setSelectbox(false);
                    $proof->setOptions(null);
                    $proof->setAcceptedValues(null);
                    if (isset($aproof['checkbox']) && $aproof['checkbox']) {
                        $proof->setCheckbox(true);
                    } elseif (isset($aproof['radio']) && $aproof['radio']) {
                        $proof->setRadio(true);
                        $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                        $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                    } elseif (isset($aproof['input']) && $aproof['input']) {
                        $proof->setInput(true);
                    } elseif (isset($aproof['file']) && $aproof['file']) {
                        $proof->setFile(true);
                    } elseif (isset($aproof['selectbox']) && $aproof['selectbox']) {
                        $proof->setSelectbox(true);
                        $proof->setOptions(isset($aproof['options']) ? $aproof['options'] : null);
                        $proof->setAcceptedValues(isset($aproof['acceptedValues']) ? $aproof['acceptedValues'] : null);
                    }
                    $structure->addStructureProof($proof);
                    $this->entityManager->persist($proof);
                }
            }
        }

        // check if operators have changed
        if (in_array('operators', array_keys($fields))) {
            $ids = [];
            foreach ($fields['operators'] as $operator) {
                if (array_key_exists('id', $operator)) {
                    $ids[] = $operator['id'];
                }
            }
            // check if an operator has been removed
            foreach ($structure->getOperates() as $operate) {
                if (!in_array($operate->getUser()->getId(), $ids)) {
                    // operator removed
                    // check if user is still an operator somewhere
                    $this->checkIsOperator($operate);
                    // remove operator for current structure
                    $structure->removeOperate($operate);
                }
            }

            // check if an operator has been added
            $ids=[];
            foreach ($structure->getOperates() as $operate) {
                $ids[] = $operate->getUser()->getId();
            }
            foreach ($fields["operators"] as $operator) {
                if (!in_array($operator['id'], $ids)) {
                    // operator added, check if the operator exists
                    if ($user = $this->userRepository->find($operator['id'])) {
                        $operate = new Operate();
                        $operate->setUser($user);
                        $structure->addOperate($operate);
                        if ($authItem = $this->authManager->getAuthItem(AuthItem::ROLE_SOLIDARY_MANAGER)) {
                            $this->authManager->grant($user, $authItem, null, false);
                        }
                    } else {
                        throw new SolidaryException(SolidaryException::UNKNOWN_USER);
                    }
                }
            }
        }

        // reorder proofs (order may have changed during the persists)
        $proofs = $structure->getStructureProofs();
        $structure->removeStructureProofs();
        usort($proofs, [$this,"comparePosition"]);
        foreach ($proofs as $proof) {
            $structure->addStructureProof($proof);
        }

        // persist the structure
        $this->entityManager->persist($structure);
        $this->entityManager->flush();
        
        // return the structure
        return $structure;
    }

    /**
     * Delete a structure
     *
     * @param Structure $structure  The structure to delete
     * @return void
     */
    public function deleteStructure(Structure $structure)
    {
        $this->entityManager->remove($structure);
        $this->entityManager->flush();
    }

    /**
     * Check if the given operate user is operator elsewhere the given structure, and eventually remove the operate role
     *
     * @param Operate $operate      The operate that containes the user and structure
     * @return bool
     */
    private function checkIsOperator(Operate $operate)
    {
        $nbStructures = 0;
        foreach ($operate->getUser()->getOperates() as $operated) {
            /**
             * @var Operate $operated
             */
            if ($operated->getStructure()->getId() !== $operate->getStructure()->getId()) {
                // the user is at least operator for another structure
                $nbStructures++;
                break;
            }
        }
        if ($nbStructures == 0) {
            // the user is not operator anymore
            if ($authItem = $this->authManager->getAuthItem(AuthItem::ROLE_SOLIDARY_MANAGER)) {
                $this->authManager->revoke($operate->getUser(), $authItem, null, false);
            }
        }
    }

    private function comparePosition($a, $b)
    {
        return strcmp($a->getPosition(), $b->getPosition());
    }
}
