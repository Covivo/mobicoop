<?php
/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\RdexPlus\Exception;

/**
 * RDEX+ Exception
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexPlusException extends \LogicException
{
    public const USER_ID_REQUIRED = "user.id is required";
    public const USER_UNKNOWN = "User Unknown";
    public const INVALID_FREQUENCY = "Invalid frequency";
    public const INVALID_PRICE_TYPE = "Invalid price type";
    public const INVALID_CARPOOLER_TYPE = "Invalid carpoolerType";
    public const FROM_LATITUDE_LONGITUDE_REQUIRED = "Origin's Latitude/Longitude are required";
    public const TO_LATITUDE_LONGITUDE_REQUIRED = "Destination's Latitude/Longitude are required";
    public const NO_REGULAR_SCHEDULE = "Missing regular schedule";
    public const NO_RETURN = "Missing return data";
    public const NO_RETURN_REGULAR_SCHEDULE = "Missing return regular schedule";
}
