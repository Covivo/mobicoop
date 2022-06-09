<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

use App\Solidary\Entity\StructureProof;
use Doctrine\ORM\EntityManagerInterface;

/**
 * StructureProof manager service in administration context.
 *
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class StructureProofManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Delete a structureProof.
     *
     * @param StructureProof $structureProof the StructureProof to delete
     */
    public function deleteStructureProof(StructureProof $structureProof)
    {
        $this->entityManager->remove($structureProof);
        $this->entityManager->flush();
    }
}
