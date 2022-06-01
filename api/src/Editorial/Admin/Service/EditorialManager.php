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

namespace App\Editorial\Admin\Service;

use App\Editorial\Entity\Editorial;
use App\Editorial\Exception\EditorialException;
use App\Editorial\Repository\EditorialRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Editorial manager for admin context.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class EditorialManager
{
    private $entityManager;
    private $editorialRepository;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EditorialRepository $editorialRepository
    ) {
        $this->entityManager = $entityManager;
        $this->editorialRepository = $editorialRepository;
    }

    /**
     * Get an editorial.
     *
     * @param int $id The editorial's id
     */
    public function getEditorial(int $id): Editorial
    {
        if (!$editorial = $this->editorialRepository->find($id)) {
            throw new EditorialException('Editorial not found');
        }

        return $editorial;
    }

    /**
     * Get all editorials.
     *
     * @return null|Editorials[]
     */
    public function getEditorials(): ?array
    {
        if (!$editorials = $this->editorialRepository->findAll()) {
            throw new EditorialException('Editorials not found');
        }

        return $editorials;
    }

    /**
     * Add an editorial.
     *
     * @param Editorial $editorial The editorial to add
     *
     * @return Editorial The editorial created
     */
    public function addEditorial(Editorial $editorial): Editorial
    {
        // persist the editorial
        $this->entityManager->persist($editorial);
        $this->entityManager->flush();

        // return the editorial
        return $editorial;
    }

    /**
     * Patch an editorial.
     *
     * @param Editorial $editorial The editorial to update
     * @param array     $fields    The updated fields
     *
     * @return Editorial The editorial updated
     */
    public function patchEditorial(Editorial $editorial, array $fields): Editorial
    {
        // persist the editorial
        $this->entityManager->persist($editorial);
        $this->entityManager->flush();

        // return the editorial
        return $editorial;
    }

    /**
     * Delete an editorial.
     *
     * @param Editorial $editorial The editorial to delete
     */
    public function deleteEditorial(Editorial $editorial): void
    {
        $this->entityManager->remove($editorial);
        $this->entityManager->flush();
    }
}
