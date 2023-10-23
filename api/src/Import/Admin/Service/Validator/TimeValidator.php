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

namespace App\Import\Admin\Service\Validator;

use App\Import\Admin\Interfaces\FieldValidatorInterface;

/**
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class DateValidator implements FieldValidatorInterface
{
    private const FORMAT = 'Y-m-d';
    private const FORMAT_ERROR_MESSAGE = 'HH:ii';

    public function validate($value): bool
    {
        if (\DateTime::createFromFormat(self::FORMAT, $value)) {
            return true;
        }

        return false;
    }

    public function errorMessage($value): string
    {
        return $value.' is not a valid time. Format must be '.self::FORMAT_ERROR_MESSAGE;
    }
}
