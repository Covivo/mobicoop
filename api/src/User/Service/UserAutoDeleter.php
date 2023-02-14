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

namespace App\User\Service;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserAutoDeleter
{
    public const FIRST_WARNING_RATIO = 0.25;
    public const LAST_WARNING_RATIO = 0.042;
    public const NB_DAYS_IN_A_MONTH = 30;

    private $_active;
    private $_period;

    public function __construct(bool $active, int $period)
    {
        $this->_active = $active;
        $this->_period = $period;
    }

    public function autoDelete()
    {
        if (!$this->_active) {
            return;
        }

        echo $this->_computeFirstWarningTimeOffsetInMonth();
        echo PHP_EOL;
        echo $this->_computeLastWarningTimeOffsetInMonth();
    }

    private function _computeFirstWarningTimeOffsetInMonth(): int
    {
        return floor($this->_period * self::FIRST_WARNING_RATIO);
    }

    private function _computeLastWarningTimeOffsetInMonth(): int
    {
        return floor($this->_period * self::LAST_WARNING_RATIO * self::NB_DAYS_IN_A_MONTH);
    }
}
