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

namespace App\Import\Admin\Service;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ImporterSanitizer
{
    public function sanitize(array $line): array
    {
        foreach ($line as $key => $field) {
            if ($this->isLatLon($field)) {
                $line[$key] = str_replace(',', '.', $field);
            }
        }

        return $line;
    }

    public function isLatLon(string $value): bool
    {
        return $this->_isValidLatitude($value) || $this->_isValidLongitude($value);
    }

    private function _isValidLatitude($lat)
    {
        // Utilisation d'une expression régulière pour vérifier si la chaîne correspond à une latitude valide
        $pattern = '/^[-]?(([0-8]?[0-9])[\.,](\d+))|(90[\.,](\d+)?)$/';

        return preg_match($pattern, $lat);
    }

    private function _isValidLongitude($lon)
    {
        // Utilisation d'une expression régulière pour vérifier si la chaîne correspond à une longitude valide
        $pattern = '/^[-]?((1[0-7]|[0-9])?(\d)[\.,](\d+))|(180[\.,](\d+)?)$/';

        return preg_match($pattern, $lon);
    }
}
