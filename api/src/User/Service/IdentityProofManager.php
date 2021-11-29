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

namespace App\User\Service;

use App\App\Repository\AppRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\User\Entity\IdentityProof;
use Exception;

/**
 * IdentityProof manager.
 *
 * This service contains methods related to IdentityProof manipulations.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class IdentityProofManager
{
    private $entityManager;

    
    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

   
    public function createIdentityProof(IdentityProof $identityProof)
    {
        if (is_null($identityProof->getUser())) {
            throw new Exception("User are mandatory", 1);
        }
        $this->entityManager->persist($identityProof);
        $this->entityManager->flush();
  
        return $identityProof;
    }

    public function updateIdentityProof(IdentityProof $identityProof)
    {
        return $identityProof;
    }
}
