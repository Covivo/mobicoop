<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Geography\Service;

use App\Geography\Entity\Territory;

/**
 * Address Territory link checker.
 *
 * This service is used to determine if the process linking addresses and territories is still running and it's not too old
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AddressTerritoryLinkChecker
{
    private const CHECK_RUNNING_FILE = 'updateAddressesAndDirections.txt';

    private $_batchTemp;

    public function __construct(string $batchTemp)
    {
        $this->_batchTemp = $batchTemp;
    }

    public function checkLockFile(\DateTime $limitDate, $autoDeleteLockFile = false)
    {
        if (!file_exists($this->_batchTemp.self::CHECK_RUNNING_FILE)) {
            return;
        }

        if ($this->_lockFileisTooOld($limitDate) && $autoDeleteLockFile) {
            $this->_removeLockFile();
        }
    }

    private function _lockFileisTooOld(\DateTime $limitDate): bool
    {
        $lastUpdatedTimestamp = filemtime($this->_batchTemp.self::CHECK_RUNNING_FILE);
        $lastUpdatedDate = \DateTime::createFromFormat('U', $lastUpdatedTimestamp);
        if ($lastUpdatedDate < $limitDate) {
            return true;
        }

        return false;
    }

    private function _removeLockFile()
    {
        unlink($this->_batchTemp.self::CHECK_RUNNING_FILE);
    }
}
