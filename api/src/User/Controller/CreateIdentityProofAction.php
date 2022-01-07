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

namespace App\User\Controller;

use App\Service\FileManager;
use App\User\Entity\IdentityProof;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

final class CreateIdentityProofAction
{
    private $user;
    private $fileManager;

    public function __construct(
        Security $security,
        FileManager $fileManager
    ) {
        $this->user = $security->getUser();
        $this->fileManager = $fileManager;
    }

    public function __invoke(Request $request): IdentityProof
    {
        $identityProof = new IdentityProof();

        $identityProof->setFile($request->files->get('file'));
        $identityProof->setUser($this->user);

        if (!empty($request->request->get('fileName'))) {
            $fileName = $this->fileManager->sanitize($request->request->get('fileName'));
        } else {
            $fileName = time();
        }
        $identityProof->setFileName($this->user->getId().'-'.$fileName);

        return $identityProof;
    }
}
