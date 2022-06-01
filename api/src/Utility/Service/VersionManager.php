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
 */

namespace App\Utility\Service;

/**
 * Version manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class VersionManager
{
    private $repositoryFile;
    private $appId;

    /**
     * Constructor.
     */
    public function __construct(string $repositoryFile, string $appId)
    {
        $this->repositoryFile = $repositoryFile;
        $this->appId = $appId;
    }

    public function getVersions()
    {
        $versions = json_decode(file_get_contents($this->repositoryFile), true);

        if (is_array($versions) && array_key_exists($this->appId, $versions)) {
            return $versions[$this->appId];
        }

        return [];
    }
}
