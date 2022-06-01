<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Match\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Match\Service\MassImportManager;
use App\Match\Entity\Mass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * Mass import controller.
 * Here we use a controller instead of a DataPersister as we need to handle multipart/form-data.
 */
final class CreateMassImportAction
{
    private $security;
    private $massImportManager;

    public function __construct(Security $security, MassImportManager $massImportManager)
    {
        $this->massImportManager = $massImportManager;
        $this->security = $security;
    }

    public function __invoke(Request $request): ?Mass
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $mass = new Mass();
        $mass->setFile($uploadedFile);

        $mass->setMassType($request->request->get('massType'));

        if ($request->request->get('checkLegit')==1) {
            $mass->setDateCheckLegit(new \Datetime());
        }

        $mass->setUser($this->security->getUser());

        return $this->massImportManager->createMass($mass);
    }
}
