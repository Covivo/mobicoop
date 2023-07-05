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

use App\Import\Admin\Interfaces\FieldValidatorInterface;
use App\Import\Admin\Interfaces\LineImportValidatorInterface;
use App\Import\Admin\Service\ImportLineValidator;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
abstract class LineImportValidator implements LineImportValidatorInterface
{
    private const NUMBER_OF_COLUMN = 0;

    private const FIELDS_VALIDATORS = [];

    public function validate(array $line, int $numLine): array
    {
        $importLineValidator = new ImportLineValidator($line, $numLine);

        $importLineValidator->validateNumberOfColumn($this->_getNumberOfColumn());

        return $importLineValidator->validateLine($line, $this->_getInstanciatedFieldsValidators());
    }

    public function _getNumberOfColumn(): int
    {
        return self::NUMBER_OF_COLUMN;
    }

    public function _getFieldsValidators(): array
    {
        return self::FIELDS_VALIDATORS;
    }

    public function _getInstanciatedFieldsValidators(): array
    {
        $validators = [];
        foreach ($this->_getFieldsValidators() as $key => $validatorClasses) {
            $validators[$key] = [];
            foreach ($validatorClasses as $validatorClass) {
                $validator = new $validatorClass();
                if (!$validator instanceof FieldValidatorInterface) {
                    throw new \LogicException('Validator '.$validatorClass.' MUST implements FieldValidatorInterface');
                }
                $validators[$key][] = $validator;
            }
        }

        return $validators;
    }
}
