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

use App\Import\Admin\Interfaces\LineImportValidatorInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserLineImportValidator implements LineImportValidatorInterface
{
    public const NUMBER_OF_COLUMN = 3;
    public const MANDATORY_PARAMETERS = [0, 1, 2];

    public function validate(array $line, int $numLine)
    {
        $importLineValidator = new ImportLineValidator($line, $numLine);

        $importLineValidator->validateNumberOfColumn(self::NUMBER_OF_COLUMN);
        foreach (self::MANDATORY_PARAMETERS as $mandatoryParameter) {
            $importLineValidator->validateMandatoryParameter($mandatoryParameter);
        }
    }
}
