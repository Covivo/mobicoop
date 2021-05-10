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

namespace App\Match\Exception;

class MassException extends \LogicException
{
    const NO_MASSPERSON = "This Mass has no MassPerson";
    const BAD_TYPE = "Bad Mass Type";

    // Migrate
    const NO_WORK_PLACES = "No work places found";
    const COMMUNITY_UNKNOWN = "Unknown community";
    const INVALIDE_COMMUNITY_COORDINATES = "Invalides coordinates for this community's address";
    const COMMUNITY_MISSING_DESCRIPTION = "Missing the community description";
    const COMMUNITY_MISSING_FULL_DESCRIPTION = "Missing community full description";
    const COMMUNITY_MISSING_ADDRESS = "Missing community address";

    // Public transport potential
    const MASS_NOT_ANALYZED = "The mass hasn't been previously analyzed";
    const UNKNOWN_TRANSPORT_MODE = "Unknown transport mode :";
}
