<?php

/**
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

namespace App\Utility\Entity\CsvMaker;

/**
 * Data Anonymizer for Csv file maker.
 *
 *@author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DataAnonymizer
{
    private const DATA_TO_ANONYMIZE = [
        'given_name',
        'family_name',
        'email',
        'telephone',
    ];

    private $_data;

    public function anonymize(array $data): array
    {
        $this->_data = $data;
        $this->_anonymizedData();

        return $this->_data;
    }

    private function _anonymizedData()
    {
        foreach ($this->_data as $key => $value) {
            if (in_array($key, self::DATA_TO_ANONYMIZE)) {
                $this->_data[$key] = $value.'_bidule';
            }
        }
    }
}
