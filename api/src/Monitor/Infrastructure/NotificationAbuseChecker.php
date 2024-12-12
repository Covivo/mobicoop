<?php

/*
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Monitor\Infrastructure;

use App\Communication\Repository\NotifiedRepository;
use App\Monitor\Core\Application\Port\Checker;

class NotificationAbuseChecker implements Checker
{
    public const OK = ['message' => 'OK'];
    public const KO = ['message' => 'KO'];

    private $_notifiedRepository;

    public function __construct(NotifiedRepository $notifiedRepository)
    {
        $this->_notifiedRepository = $notifiedRepository;
    }

    public function check(): string
    {
        $abuses = $this->_notifiedRepository->findNotifiedAbuses();
        if (count($abuses) > 0) {
            $return = self::KO;
            $return['details'] = $abuses;

            return json_encode($return, JSON_UNESCAPED_SLASHES);
        }

        return json_encode(self::OK);
    }
}
