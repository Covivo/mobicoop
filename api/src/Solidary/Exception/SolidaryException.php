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

use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryVolunteer;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryException extends \LogicException
{
    public const INVALID_DATA_PROVIDED = "Invalid data provided";
    public const NO_ROLE = "This SolidaryUser has no role";
    public const BAD_SOLIDARYUSERSTRUCTURE_STATUS = "Invalid status value for this SolidaryUserStructure";
    public const BAD_SOLIDARY_ACTION = "Unknown action";
    public const UNKNOWN_USER = "Unknown User";
    public const NO_STRUCTURE_ID = "No structureId";
    public const NO_STRUCTURE = "No structure found";
    public const TYPE_SOLIDARY_USER_UNKNOWN = "Unknown solidaryUser type (must be '".SolidaryBeneficiary::TYPE."' or '".SolidaryVolunteer::TYPE."')";
    public const ALREADY_USER = "Already a User with this email";
    public const ALREADY_SOLIDARY_USER = "Already a SolidaryUser";
    public const MANDATORY_EMAIL = "Email is mandatory and cannot be empty";
    public const MANDATORY_EMAIL_OR_PHONE = "Email or phone is mandatory and cannot be empty";
    public const INVALID_PROGRESSION = "Invalid progression provided";
    public const NO_HOME_ADDRESS = "No homeAddress";
    public const NO_OUTWARD_TIMES = "There is no outward times";
    public const NO_RETURN_TIMES = "There is no return times";
    public const DAY_CHECK_BUT_NO_OUTWARD_TIME = "You selected a day in Days array but there no outward time";
    public const DAY_CHECK_BUT_NO_RETURN_TIME = "You selected a day in Days array but there no return time";

    // SolidarySolution
    public const IS_NOT_VOLUNTEER = "The SolidaryUser is not a volunteer";
    public const CANT_HAVE_BOTH = "A SolidarySolution can't have both a Matching and a SolidaryUser";
    public const SOLIDARY_MATCHING_ALREADY_USED = "The SolidaryMatching already have a SolidarySolution";

    // Solidary matching
    public const INVALID_HOUR_SLOT = "Hour slot invalid";
    public const NO_RETURN_PROPOSAL = "There is no return proposal";
    public const NO_VALID_ADDRESS = "No valid address";
    public const NOT_A_DRIVER = "This User is not a driver";

    // Solidary Formal Request
    public const NO_SOLIDARY_SOLUTION = "No solidarySolution found for this formal request";
    public const NO_SOLIDARY_ASK = "No SolidaryAsk for this SolidarySolution";
    public const BAD_SOLIDARY_ASK_STATUS_FOR_FORMAL = "The status of the SolidaryAsk doesn't allow a formal request";
    public const SOLIDARY_SOLUTION_MISSING = "SolidarySolution is missing";
    public const SOLIDARY_SOLUTION_ID_INVALID = "Invalid SolidarySolutionId";

    // SolidarySearch
    public const SOLIDARY_MISSING = "Solidary is missing";
    public const SOLIDARY_ID_INVALID = "Invalid SolidaryId";
    public const WAY_MISSING_OR_INVALID = "Way parameter is missing or invalid (must be outward or return)";
    public const TYPE_MISSING_OR_INVALID = "Type parameter is missing or invalid (must be carpool or transport)";
    public const UNKNOWN_SOLIDARY = "There no Solidary with this id";

    // SolidaryUser
    public const NO_SOLIDARY_USER = "This User is not a Solidary User";
    public const SOLIDARY_USER_ID_INVALID = "Invalid SolidaryUserId";
    public const NO_SOLIDARY_BENEFICIARY = "This Solidary User is not a beneficiary";
    public const NO_SOLIDARY_VOLUNTEER = "This Solidary User is not a volunteer";
    public const ALREADY_ACCEPTED = "This SolidaryUser has already be accepted for the current structure";
    public const ALREADY_REFUSED = "This SolidaryUser has already be refused for the current structure";

    // Structure Geolocation
    public const MISSING_LATITUDE = "Latitude missing";
    public const MISSING_LONGITUDE = "Longitude missing";

    // SolidaryVolunteerPlanning
    public const NO_SOLIDARY_VOLUNTEER_ID = "No SolidaryVolunteerId found";
    public const SOLIDARY_VOLUNTEER_ID_INVALID = "Invalid SolidaryVolunteerId";

    // Proof
    public const NO_ID = "Missing Solidary or SolidaryVolunteer";
    public const SOLIDARY_NOT_FOUND = "Solidary not found";
    public const SOLIDARY_USER_NOT_FOUND= "SolidaryUser not found";
    public const NO_FILE = "Missing file";
    public const NO_STRUCTURE_PROOF = "Missing StructureProof";
    public const STRUCTURE_PROOF_NOT_FOUND = "StructureProof not found";
    public const STRUCTURE_PROOF_NOT_FILE = "This structure_proof is not file type";
    public const PROOF_ALREADY_EXISTS = "A proof of this StructureProof type for this Solidary already exists";

    // Territory
    public const TERRITORY_INVALID = "Invalid territory";


    // Admin exception
    public const BENEFICIARY_REQUIRED = "Beneficiary is required";
}
