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

use App\Service\FileManager;
use App\Solidary\Entity\Proof;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\Request;

final class CreateProofAction
{
    use TranslatorTrait;
    private $structureProofRepository;
    private $solidaryUserStructureRepository;
    private $solidaryRepository;
    private $fileManager;
    
    public function __construct(
        StructureProofRepository $structureProofRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        SolidaryRepository $solidaryRepository,
        FileManager $fileManager
    ) {
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->solidaryRepository = $solidaryRepository;
        $this->fileManager = $fileManager;
    }
    
    public function __invoke(Request $request): Proof
    {
        if (is_null($request)) {
            throw new \InvalidArgumentException($this->translator->trans("Bad request"));
        }

        $proof = new Proof();

        $file = $request->files->get('file');
        if (empty($request->files->get('file'))) {
            throw new SolidaryException(SolidaryException::NO_FILE);
        }

        if (empty($request->request->get('solidary'))) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_ID);
        }
        $solidaryId = $request->request->get('solidary');
        if (strrpos($solidaryId, '/')) {
            $solidaryId = substr($solidaryId, strrpos($solidaryId, '/') + 1);
        }
        $solidary = $this->solidaryRepository->find($solidaryId);
        if (is_null($solidary)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_NOT_FOUND);
        }

        if (empty($request->request->get('structureProof'))) {
            throw new SolidaryException(SolidaryException::NO_STRUCTURE_PROOF);
        }
        $structureProofId = $request->request->get('structureProof');
        if (strrpos($structureProofId, '/')) {
            $structureProofId = substr($structureProofId, strrpos($structureProofId, '/') + 1);
        }
        $structureProof = $this->structureProofRepository->find($structureProofId);
        if (is_null($structureProof)) {
            throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_NOT_FOUND);
        }
        if (!$structureProof->isFile()) {
            throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_NOT_FILE);
        }

        // We check if there is already a similar proof
      
        $proof->setFile($file);

        $proof->setStructureProof($structureProof);
        $proof->setSolidaryUserStructure($solidary->getSolidaryUserStructure());
        
        if (!empty($request->request->get('fileName'))) {
            $fileName = $this->fileManager->sanitize($request->request->get('fileName'));
        } else {
            $fileName = time();
        }
        $proof->setFileName($solidary->getId()."-".$fileName);
        

        return $proof;
    }
}
