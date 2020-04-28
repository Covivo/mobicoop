<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Exception;

class SolidaryException extends \LogicException
{
    const INVALID_DATA_PROVIDED = "Invalid data provided";
    const NO_ROLE = "This SolidaryUser has no role";
    const BAD_SOLIDARYUSERSTRUCTURE_STATUS = "Invalid status value for this SolidaryUserStructure";
    const BAD_SOLIDARY_ACTION = "Unknown action";
    const UNKNOWN_USER = "Unknown User";

    // SolidarySolution
    const IS_NOT_VOLUNTEER = "The SolidaryUser is not a volunteer";
    const CANT_HAVE_BOTH = "A SolidarySolution can't have both a Matching and a SolidaryUser";
    const NO_SOLIDARY_ASK = "No SolidaryAsk for this SolidarySolution";
    const BAD_SOLIDARY_ASK_STATUS_FOR_FORMAL = "The status of the SolidaryAsk doesn't allow a formal request";

    // Solidary matching
    const INVALID_HOUR_SLOT = "Hour slot invalid";
    const NO_RETURN_PROPOSAL = "There is no return proposal";
    const NO_VALID_ADDRESS = "No valid address";
    const NOT_A_DRIVER = "This User is not a driver";
}
