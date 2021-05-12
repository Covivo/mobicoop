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

use App\Solidary\Entity\Need;
use App\Solidary\Entity\Structure;
use App\Solidary\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Structure manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class StructureManager
{
    private $entityManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Add a structure.
     *
     * @param Structure     $structure              The structure to add
     * @return Structure    The structure created
     */
    public function addStructure(Structure $structure)
    {
        // persist the structure
        $this->entityManager->persist($structure);
        $this->entityManager->flush();

        return $structure;
    }

    /**
     * Patch a structure.
     *
     * @param Structure $structure    The structure to update
     * @param array $fields             The updated fields
     * @return Structure   The structure updated
     */
    public function patchStructure(Structure $structure, array $fields)
    {
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
                    $need->addStructure($structure);
                    $structure->addNeed($need);
                    $this->entityManager->persist($need);
                }
            }
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
}
