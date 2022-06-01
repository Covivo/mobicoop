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

namespace App\Solidary\Admin\Controller;

use App\Solidary\Admin\Service\SolidaryManager;
use App\Solidary\Entity\Proof;
use Symfony\Component\HttpFoundation\Request;

/**
 * Upload a proof in solidary admin context.
 * Used both for creating a new solidary record and updating an existing solidary record.
 */
final class UploadProofAction
{
    public function __construct(
        SolidaryManager $solidaryManager
    ) {
        $this->solidaryManager = $solidaryManager;
    }

    public function __invoke(Request $request): Proof
    {
        return $this->solidaryManager->createProof(
            $request->files->get('file'),
            $request->files->get('filename'),
            $request->request->get('solidaryId'),
            $request->request->get('proofId')
        );
    }
}
