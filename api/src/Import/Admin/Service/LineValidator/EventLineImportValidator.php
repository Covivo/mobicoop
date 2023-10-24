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

namespace App\Import\Admin\Service\LineValidator;

use App\Import\Admin\Interfaces\LineImportValidatorInterface;

/**
 * @author Rémi Wortemann <remi.worttemann@mobicoop.org>
 */
class EventLineImportValidator extends LineImportValidator implements LineImportValidatorInterface
{
    private const NUMBER_OF_COLUMN = 10;

    private const FIELDS_VALIDATORS = [
        0 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\StringValidator',
        ],
        1 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\StringValidator',
        ],
        2 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\DateValidator',
        ],
        3 => [
            'App\Import\Admin\Service\Validator\EmptyOrTimeValidator',
        ],
        4 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\DateValidator',
        ],
        5 => [
            'App\Import\Admin\Service\Validator\EmptyOrTimeValidator',
        ],
        6 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\StringValidator',
            'App\Import\Admin\Service\Validator\Max512CharactersValidator',
        ],
        7 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\LatitudeValidator',
        ],
        8 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\LongitudeValidator',
        ],
        9 => [
            'App\Import\Admin\Service\Validator\NotEmptyValidator',
            'App\Import\Admin\Service\Validator\IntValidator',
        ],
    ];

    public function _getNumberOfColumn(): int
    {
        return self::NUMBER_OF_COLUMN;
    }

    public function _getFieldsValidators(): array
    {
        return self::FIELDS_VALIDATORS;
    }
}
