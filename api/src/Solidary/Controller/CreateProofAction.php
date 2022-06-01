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
use App\Solidary\Repository\SolidaryUserRepository;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\Request;

final class CreateProofAction
{
    use TranslatorTrait;
    private $structureProofRepository;
    private $solidaryUserStructureRepository;
    private $solidaryRepository;
    private $solidaryUserRepository;
    private $fileManager;

    public function __construct(
        StructureProofRepository $structureProofRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        SolidaryRepository $solidaryRepository,
        SolidaryUserRepository $solidaryUserRepository,
        FileManager $fileManager
    ) {
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->solidaryRepository = $solidaryRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->fileManager = $fileManager;
    }

    public function __invoke(Request $request): Proof
    {
        if (is_null($request)) {
            throw new \InvalidArgumentException($this->translator->trans("Bad request"));
        }

        $proof = new Proof();

        if (empty($request->request->get('solidary')) && empty($request->request->get('solidaryVolunteer'))) {
            throw new SolidaryException(SolidaryException::NO_ID);
        }

        if (!empty($request->request->get('solidary')) && empty($request->request->get('solidaryVolunteer'))) {
            $solidaryId = $request->request->get('solidary');
            if (strrpos($solidaryId, '/')) {
                $solidaryId = substr($solidaryId, strrpos($solidaryId, '/') + 1);
            }
            $solidary = $this->solidaryRepository->find($solidaryId);
            if (is_null($solidary)) {
                throw new SolidaryException(SolidaryException::SOLIDARY_NOT_FOUND);
            }
        }

        if (empty($request->request->get('solidary')) && !empty($request->request->get('solidaryVolunteer'))) {
            $solidaryVolunteerId = $request->request->get('solidaryVolunteer');
            if (strrpos($solidaryVolunteerId, '/')) {
                $solidaryVolunteerId = substr($solidaryVolunteerId, strrpos($solidaryVolunteerId, '/') + 1);
            }
            $solidaryVolunteer = $this->solidaryUserRepository->find($solidaryVolunteerId);
            if (is_null($solidaryVolunteer)) {
                throw new SolidaryException(SolidaryException::SOLIDARY_USER_NOT_FOUND);
            }
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
        if ($structureProof->isFile() && empty($request->files->get('file'))) {
            throw new SolidaryException(SolidaryException::NO_FILE);
        }

        // If there is a file, we need to do a special treatment
        $file = $request->files->get('file');


        if (!empty($request->files->get('file'))) {
            if (!$structureProof->isFile()) {
                throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_NOT_FILE);
            }
            $proof->setFile($file);

            if (!empty($request->request->get('fileName'))) {
                $fileName = $this->fileManager->sanitize($request->request->get('fileName'));
            } else {
                $fileName = time();
            }
            $proof->setFileName($structureProof->getId()."-".$fileName);
        }

        if (isset($solidary)) {
            $solidaryUserStructure=$solidary->getSolidaryUserStructure();
        } elseif (isset($solidaryVolunteer)) {
            $solidaryUserStructure=$this->solidaryUserStructureRepository->findByStructureAndSolidaryUser($structureProof->getStructure()->getId(), $solidaryVolunteer->getId());
        }

        $proof->setValue($request->request->get('value'));
        $proof->setStructureProof($structureProof);
        $proof->setSolidaryUserStructure($solidaryUserStructure);

        return $proof;
    }
}
