<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Validator\Phone;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
abstract class Validator
{
    protected $next;
    protected $phoneNumberUtil;

    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function validate(string $phone): bool
    {
        if ($this->isValid($phone)) {
            return true;
        }

        if (isset($this->next)) {
            return $this->next->validate($phone);
        }

        return false;
    }

    public function setNext(self $next): void
    {
        $this->next = $next;
    }

    protected function isValid(string $phone): bool
    {
        $phoneNumber = $this->parse($phone, $this->getRegion());
        if (!is_null($phoneNumber->getNationalNumber())) {
            return $this->phoneNumberUtil->isValidNumberForRegion($phoneNumber, $this->getRegion());
        }

        return false;
    }

    protected function parse(string $phone, string $region)
    {
        try {
            return $this->phoneNumberUtil->parse($phone, $region);
        } catch (NumberParseException $exception) {
            return new PhoneNumber();
        }
    }

    abstract protected function getRegion(): string;
}
