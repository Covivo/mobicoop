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
        'given_name' => '',
        'family_name' => '',
        'email' => '_anonymizeEmail',
        'telephone' => '_anonymizeTelephone',
    ];

    private const EMAIL_ANONYMIZED_DOMAIN = 'xyz.io';
    private const TELEPHONE_ANONYMIZED = '0606060606';

    private $_data;

    public function anonymize(array $data): array
    {
        $this->_data = $data;
        $this->_anonymizeData();

        return $this->_data;
    }

    private function _anonymizeData()
    {
        foreach ($this->_data as $key => $value) {
            if (isset(self::DATA_TO_ANONYMIZE[$key])) {
                if ('' !== self::DATA_TO_ANONYMIZE[$key]) {
                    $this->_data[$key] = $this->{self::DATA_TO_ANONYMIZE[$key]}();
                } else {
                    $this->_data[$key] = $this->_generateRandomString(20);
                }
            }
        }
    }

    private function _anonymizeEmail(): string
    {
        $base = '';
        if (isset($this->_data['userId'])) {
            $base = $this->_data['userId'];
        }

        return $base.'@'.self::EMAIL_ANONYMIZED_DOMAIN;
    }

    private function _anonymizeTelephone(): string
    {
        return self::TELEPHONE_ANONYMIZED;
    }

    private function _generateRandomString(int $length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
