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

namespace App\Import\Admin\Controller;

use App\Import\Admin\Resource\Import;
use App\Import\Admin\Service\Importer;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class ImportUsersAction
{
    public function __invoke(Request $request): Import
    {
        if (!$request->files->get('file')) {
            throw new \Exception('File is mandatory');
        }

        $importer = new Importer($request->files->get('file'), $request->get('filename'));

        return $importer->importUsers();
    }
}
