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

namespace App\Editorial\Service;

use App\Editorial\Entity\Editorial;
use App\Editorial\Exception\EditorialException;
use Doctrine\ORM\EntityManagerInterface;
use App\Editorial\Repository\EditorialRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Editorial manager.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class EditorialManager
{
    private $editorialRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EditorialRepository $editorialRepository
    ) {
        $this->editorialRepository = $editorialRepository;
    }

    /**
     * Get the activated editorial
     *
     * @return Editorial
     */
    public function getActivatedEditorial(): Editorial
    {
        if (!$editorial = $this->editorialRepository->findOneBy(['status' => Editorial::STATUS_ACTIVE])) {
            throw new EditorialException('No activated editorial content');
        }
        return $editorial;
    }
}
