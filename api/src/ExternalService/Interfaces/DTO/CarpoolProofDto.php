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
    public const STATUS_INITIATED = 0;              // not ready to be sent, proof still under construction
    public const STATUS_PENDING = 1;                // ready to be sent
    public const STATUS_SENT = 2;                   // sent
    public const STATUS_ERROR = 3;                  // error during the sending
    public const STATUS_CANCELED = 4;               // cancellation before sending
    public const STATUS_ACQUISITION_ERROR = 5;      // proof not recorded by the carpool register
    public const STATUS_NORMALIZATION_ERROR = 6;    // proof recorded but data not normalized by the carpool register
    public const STATUS_FRAUD_ERROR = 7;            // fraud detected by carpool register
    public const STATUS_VALIDATED = 8;              // proof validated by the carpool register
    public const STATUS_EXPIRED = 9;                // proof sent too late to the carpool register
    public const STATUS_CANCELED_BY_OPERATOR = 10;  // proof canceled by the operator
    public const STATUS_UNDER_CHECKING = 11;        // proof under review by the carpool register
    public const STATUS_UNKNOWN = 12;               // status unknown by the RPC (proof exists but... unknown)
    public const STATUS_INVALID_CONCURRENT_SCHEDULES = 13; // proof not sent: concurrent travel at the same time already sent to rpc
    public const STATUS_INVALID_SPLITTED_TRIP = 14; // proof not sent: a long trip has been splitted
    public const STATUS_INVALID_DUPLICATE_DEVICE = 15; // proof not sent: passenger and driver phone unique id are indentical

    public const TYPE_LOW = 'A';
    public const TYPE_MID = 'B';
    public const TYPE_HIGH = 'C';

    public const TYPE_UNDETERMINED_CLASSIC = 'CX';
    public const TYPE_UNDETERMINED_DYNAMIC = 'DX';

    /**
     * @var int the id of this proof
     */
    private $id;

    /**
     * @var string register system proof type
     */
    private $type;
}
