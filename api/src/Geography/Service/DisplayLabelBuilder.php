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
use App\Geography\Entity\Address;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DisplayLabelBuilder
{
    // Will use the first one find if not already used in another line
    private const MATCHING_FIELDS = [
        'street' => ['streetAddress', 'street', 'addressLocality'],
        'locality' => ['addressLocality', 'addressCountry'],
        'postalCode' => ['postalCode'],
    ];
    private $_carpoolDisplayFieldsOrder;
    private $_waypoint;
    private $_displayLabel;

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

        $this->_formatWithCustomOrder();

        return $this->_displayLabel;
    }

    public function getCarpoolDisplayFieldsOrder(): array
    {
        return $this->_carpoolDisplayFieldsOrder;
    }

    public function _inArrayInSubArrays($needle, $haystack)
    {
        foreach ($haystack as $subArray) {
            if (in_array($needle, $subArray)) {
                return true;
            }
        }

        return false;
    }

    private function _formatWithCustomOrder()
    {
        $this->_displayLabel = [];
        $address = $this->_waypoint->getAddress();
        foreach ($this->_carpoolDisplayFieldsOrder as $lineOrder) {
            $this->_displayLabel[] = $this->_treatOrderLine($address, $lineOrder);
        }
    }

    private function _determineRelevantValue(Address $address, string $field): ?string
    {
        if (isset(self::MATCHING_FIELDS[$field])) {
            foreach (self::MATCHING_FIELDS[$field] as $correspondingField) {
                if (method_exists($address, 'get'.ucfirst($correspondingField))) {
                    $value = call_user_func([$address, 'get'.ucfirst($correspondingField)]);
                    if ('' !== trim($value) && !$this->_inArrayInSubArrays($value, $this->_displayLabel)) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    private function _treatOrderLine(Address $address, array $lineOrder): array
    {
        $line = [];
        foreach ($lineOrder as $field) {
            $value = $this->_determineRelevantValue($address, $field);
            if (!is_null($value) && '' !== trim($value)) {
                $line[] = $value;
            }
        }

        return $line;
    }
}
