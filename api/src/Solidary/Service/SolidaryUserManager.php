<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Service;

use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\StructureProof;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Exception\SolidaryException;

class SolidaryUserManager
{
    private $entityManager;
    private $userManager;
    private $structureRepository;
    private $structureProofRepository;
    private $solidaryUserRepository;

    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, StructureRepository $structureRepository, StructureProofRepository $structureProofRepository, SolidaryUserRepository $solidaryUserRepository)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->structureRepository = $structureRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
    }
}
