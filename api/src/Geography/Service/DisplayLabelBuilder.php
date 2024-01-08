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

use App\Carpool\Entity\Waypoint;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DisplayLabelBuilder
{
    private $_carpoolDisplayFieldsOrder;
    private $_waypoint;

    public function __construct(array $carpoolDisplayFieldsOrder)
    {
        $this->_carpoolDisplayFieldsOrder = $carpoolDisplayFieldsOrder;
    }

    public function buildDisplayLabelFromWaypoint(Waypoint $waypoint): array
    {
        $this->_waypoint = $waypoint;

        if (0 == count($this->_carpoolDisplayFieldsOrder)) {
            return [];
        }

        if (is_null($this->_waypoint->getAddress())) {
            return [];
        }

        return $this->_formatWithCustomOrder();
    }

    public function getCarpoolDisplayFieldsOrder(): array
    {
        return $this->_carpoolDisplayFieldsOrder;
    }

    private function _formatWithCustomOrder(): array
    {
        $displayLabel = [];
        $address = $this->_waypoint->getAddress();
        foreach ($this->_carpoolDisplayFieldsOrder as $lineOrder) {
            $line = [];
            foreach ($lineOrder as $field) {
                if (method_exists($address, 'get'.ucfirst($field))) {
                    $line[] = call_user_func([$address, 'get'.ucfirst($field)]);
                }
            }
            $displayLabel[] = $line;
        }

        return $displayLabel;
    }
}
