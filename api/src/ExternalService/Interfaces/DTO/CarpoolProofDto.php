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

namespace App\ExternalService\Interfaces\DTO;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofDto
{
    public const TYPE_LOW = 'A';
    public const TYPE_MID = 'B';
    public const TYPE_HIGH = 'C';

    public const TYPES = [
        self::TYPE_LOW,
        self::TYPE_MID,
        self::TYPE_HIGH,
    ];

    /**
     * @var int
     */
    private $journeyId;

    /**
     * @var string register system proof type : see TYPES
     */
    private $operatorClass;

    /**
     * @var ActorDto
     */
    private $passenger;

    /**
     * @var ActorDto
     */
    private $driver;
}