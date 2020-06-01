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

namespace App\Solidary\Controller;

use App\Solidary\Entity\Proof;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CreateProofAction
{
    use TranslatorTrait;
    private $structureProofRepository;
    private $solidaryUserStructureRepository;
    private $logger;
    
    public function __construct(StructureProofRepository $structureProofRepository, SolidaryUserStructureRepository $solidaryUserStructureRepository, LoggerInterface $logger)
    {
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->logger = $logger;
    }
    
    public function __invoke(Request $request): Proof
    {
        if (is_null($request)) {
            throw new \InvalidArgumentException($this->translator->trans("Bad request"));
        }

        $proof = new Proof();

        $proof->setFile($request->files->get('file'));

        //echo $request->request->get('structureProof');die;
        $proof->setStructureProof($this->structureProofRepository->find(1));
        $proof->setSolidaryUserStructure($this->solidaryUserStructureRepository->find(1));
        $proof->setFileName($request->request->get('fileName'));
        

        return $proof;
    }
}
