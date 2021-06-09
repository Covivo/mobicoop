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

namespace App\Solidary\Admin\Controller;

use App\Service\FileManager;
use App\Solidary\Admin\Exception\SolidaryException;
use App\Solidary\Entity\Proof;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\StructureProofRepository;
use Symfony\Component\HttpFoundation\Request;

final class UploadProofAction
{
    private $structureProofRepository;
    private $solidaryRepository;
    private $fileManager;
    
    public function __construct(
        SolidaryRepository $solidaryRepository,
        StructureProofRepository $structureProofRepository,
        FileManager $fileManager
    ) {
        $this->solidaryRepository = $solidaryRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->fileManager = $fileManager;
    }
    
    public function __invoke(Request $request): Proof
    {
        $proof = new Proof();
        if (!$solidaryId = $request->request->get('solidaryId')) {
            throw new SolidaryException(SolidaryException::SOLIDARY_ID_REQUIRED);
        }
        if (!$solidary = $this->solidaryRepository->find($solidaryId)) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_NOT_FOUND, $solidaryId));
        }

        if (!$structureProofId = $request->request->get('proofId')) {
            throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_ID_REQUIRED);
        }
        if (!$structureProof = $this->structureProofRepository->find($structureProofId)) {
            throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_PROOF_NOT_FOUND, $structureProofId));
        }

        if ($structureProof->isFile() && !$file = $request->files->get('file')) {
            throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_PROOF_FILE_REQUIRED, $structureProofId));
        }

        $proof->setFile($file);

        if (!empty($request->request->get('filename'))) {
            $fileName = $this->fileManager->sanitize($request->request->get('filename'));
        } else {
            $fileName = microtime();
        }
        $proof->setFileName($solidary->getId()."-".$fileName);
  
        $proof->setStructureProof($structureProof);
        $proof->setSolidaryUserStructure($solidary->getSolidaryUserStructure());

        return $proof;
    }
}
