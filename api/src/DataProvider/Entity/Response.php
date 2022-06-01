<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\DataProvider\Entity;

/**
 * A DataProvider response.
 */
class Response
{
    public const DEFAULT_CODE = 404;

    /**
     * @var int $code The response code.
     */
    private $code;

    /**
     * @var object|array $value The value of the response.
     */
    private $value;

    public function __construct(int $code=self::DEFAULT_CODE, $value=null)
    {
        $this->setCode($code);
        $this->setValue($value);
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
