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
    const INVALID_DATA_PROVIDED = "Invalid data provided";
    const NO_ROLE = "This SolidaryUser has no role";
    const BAD_SOLIDARYUSERSTRUCTURE_STATUS = "Invalid status value for this SolidaryUserStructure";
    const BAD_SOLIDARY_ACTION = "Unknown action";
    const UNKNOWN_USER = "Unknown User";
    const NO_STRUCTURE_ID = "No structureId";
    const NO_STRUCTURE = "No structure found";
    const TYPE_SOLIDARY_USER_UNKNOWN = "Unknown solidaryUser type (must be '".SolidaryBeneficiary::TYPE."' or '".SolidaryVolunteer::TYPE."')";
    const ALREADY_USER = "Already a User with this email";
    const ALREADY_SOLIDARY_USER = "Already a SolidaryUser";
    const MANDATORY_EMAIL = "Email is mandatory and cannot be empty";
    const MANDATORY_EMAIL_OR_PHONE = "Email or phone is mandatory and cannot be empty";
    const INVALID_PROGRESSION = "Invalid progression provided";
    const NO_HOME_ADDRESS = "No homeAddress";
    const NO_OUTWARD_TIMES = "There is no outward times";
    const NO_RETURN_TIMES = "There is no return times";
    const DAY_CHECK_BUT_NO_OUTWARD_TIME = "You selected a day in Days array but there no outward time";
    const DAY_CHECK_BUT_NO_RETURN_TIME = "You selected a day in Days array but there no return time";
    
    // SolidarySolution
    const IS_NOT_VOLUNTEER = "The SolidaryUser is not a volunteer";
    const CANT_HAVE_BOTH = "A SolidarySolution can't have both a Matching and a SolidaryUser";
    const SOLIDARY_MATCHING_ALREADY_USED = "The SolidaryMatching already have a SolidarySolution";

    // Solidary matching
    const INVALID_HOUR_SLOT = "Hour slot invalid";
    const NO_RETURN_PROPOSAL = "There is no return proposal";
    const NO_VALID_ADDRESS = "No valid address";
    const NOT_A_DRIVER = "This User is not a driver";

    // Solidary Formal Request
    const NO_SOLIDARY_SOLUTION = "No solidarySolution found for this formal request";
    const NO_SOLIDARY_ASK = "No SolidaryAsk for this SolidarySolution";
    const BAD_SOLIDARY_ASK_STATUS_FOR_FORMAL = "The status of the SolidaryAsk doesn't allow a formal request";
    const SOLIDARY_SOLUTION_MISSING = "SolidarySolution is missing";
    const SOLIDARY_SOLUTION_ID_INVALID = "Invalid SolidarySolutionId";

    // SolidarySearch
    const SOLIDARY_MISSING = "Solidary is missing";
    const SOLIDARY_ID_INVALID = "Invalid SolidaryId";
    const WAY_MISSING_OR_INVALID = "Way parameter is missing or invalid (must be outward or return)";
    const TYPE_MISSING_OR_INVALID = "Type parameter is missing or invalid (must be carpool or transport)";
    const UNKNOWN_SOLIDARY = "There no Solidary with this id";

    // SolidaryUser
    const NO_SOLIDARY_USER = "This User is not a Solidary User";
    const SOLIDARY_USER_ID_INVALID = "Invalid SolidaryUserId";
    const NO_SOLIDARY_BENEFICIARY = "This Solidary User is not a beneficiary";
    const NO_SOLIDARY_VOLUNTEER = "This Solidary User is not a volunteer";
    const ALREADY_ACCEPTED = "This SolidaryUser has already be accepted for the current structure";
    const ALREADY_REFUSED = "This SolidaryUser has already be refused for the current structure";

    // Structure Geolocation
    const MISSING_LATITUDE = "Latitude missing";
    const MISSING_LONGITUDE = "Longitude missing";

    // SolidaryVolunteerPlanning
    const NO_SOLIDARY_VOLUNTEER_ID = "No SolidaryVolunteerId found";
    const SOLIDARY_VOLUNTEER_ID_INVALID = "Invalid SolidaryVolunteerId";

    // Proof
    const NO_ID = "Missing Solidary or SolidaryVolunteer";
    const SOLIDARY_NOT_FOUND = "Solidary not found";
    const SOLIDARY_USER_NOT_FOUND= "SolidaryUser not found";
    const NO_FILE = "Missing file";
    const NO_STRUCTURE_PROOF = "Missing StructureProof";
    const STRUCTURE_PROOF_NOT_FOUND = "StructureProof not found";
    const STRUCTURE_PROOF_NOT_FILE = "This structure_proof is not file type";
    const PROOF_ALREADY_EXISTS = "A proof of this StructureProof type for this Solidary already exists";

    // Territory
    const TERRITORY_INVALID = "Invalid territory";


    // Admin exception
    const BENEFICIARY_REQUIRED = "Beneficiary is required";
}
