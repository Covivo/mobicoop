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
class ImportLineValidator
{
    public const NUMBER_OF_COLUMN = 2;

    /**
     * @var array
     */
    private $_line;

    /**
     * @var int
     */
    private $_numLine;

    public function __construct(array $line, int $numLine)
    {
        $this->_line = $line;
        $this->_numLine = $numLine;
    }

    public function validateNumberOfColumn(int $numberOfColumn)
    {
        if ($numberOfColumn != count($this->_line)) {
            throw new \LogicException('Incorrect number of column line '.$this->_numLine.' ('.$numberOfColumn.' expected)');
        }
    }

    public function validateLine(array $line, array $fieldsValidators): array
    {
        $errors = [];
        foreach ($line as $key => $field) {
            if (isset($fieldsValidators[$key])) {
                foreach ($fieldsValidators[$key] as $fieldValidator) {
                    if (!$fieldValidator->validate($field)) {
                        $errors[] = $fieldValidator->errorMessage($field).' for line '.json_encode($line);
                    }
                }
            }
        }

        return $errors;
    }
}
