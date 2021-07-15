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

namespace App\Solidary\Admin\Exception;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryException extends \LogicException
{
    // beneficiary
    const BENEFICIARY_REQUIRED = "Beneficiary is required";
    const BENEFICIARY_NOT_FOUND = "Beneficiary #%s not found";
    const BENEFICIARY_VALIDATION_VALUE_REQUIRED = "Validation value is required";
    const BENEFICIARY_VALIDATION_ID_REQUIRED = "Validation user structure id is required";

    // volunteer
    const VOLUNTEER_NOT_FOUND = "Volunteer #%s not found";
    const VOLUNTEER_VALIDATION_VALUE_REQUIRED = "Validation value is required";
    const VOLUNTEER_VALIDATION_ID_REQUIRED = "Validation user structure id is required";
    
    // structure
    const STRUCTURE_REQUIRED = "Structure is required";
    const STRUCTURE_NOT_FOUND = "Structure #%s not found";

    // solidary user structure
    const SOLIDARY_USER_STRUCTURE_NOT_FOUND = "Solidary user stucture #%s not found";

    // journey
    const ORIGIN_REQUIRED = "Origin is required";
    const DESTINATION_REQUIRED = "Destination is required";
    const REGULAR_MIN_DATE_REQUIRED = "Regular min date is required";
    const REGULAR_MAX_DATE_REQUIRED = "Regular max date is required";
    const FREQUENCY_REQUIRED = "Frequency (as 'regular' boolean) is required";
    const REGULAR_SCHEDULES_REQUIRED = "Regular schedules are required";
    const REGULAR_DATE_CHOICE_REQUIRED = "Regular date choice is required";
    const PUNCTUAL_OUTWARD_DATE_CHOICE_REQUIRED = "Punctual date choice is required";
    const PUNCTUAL_OUTWARD_DATE_CHOICE_INVALID = "Punctual date choice is invalid";
    const PUNCTUAL_OUTWARD_TIME_CHOICE_REQUIRED = "Punctual time choice is required";
    const PUNCTUAL_OUTWARD_TIME_CHOICE_INVALID = "Punctual time choice is invalid";
    
    // structure proof
    const STRUCTURE_PROOF_ID_REQUIRED = "Structure proof id is required";
    const STRUCTURE_PROOF_VALUE_REQUIRED = "Structure proof value is required for proof #%s";
    const STRUCTURE_PROOF_FILE_REQUIRED = "Structure proof file is required for proof #%s";
    const STRUCTURE_PROOF_NOT_FOUND = "Structure proof #%s not found";

    // subject
    const SUBJECT_NOT_FOUND = "Subject #%s not found";

    // need
    const NEED_NOT_FOUND = "Need #%s not found";

    // solidary
    const SOLIDARY_ID_REQUIRED = "Solidary id is required";
    const SOLIDARY_NOT_FOUND = "Solidary record #%s not found";

    // solidary action
    const SOLIDARY_ACTION_REQUIRED = "Solidary action is required";
    const SOLIDARY_ACTION_USER_REQUIRED = "User is required";
    const SOLIDARY_ACTION_NOT_FOUND = "Solidary action #%s not found";
    const SOLIDARY_ACTION_USER_NOT_FOUND = "User #%s not found";

    // solidary solution
    const SOLIDARY_SOLUTION_MATCHING_REQUIRED= "A matching id is required";
    const SOLIDARY_SOLUTION_MATCHING_NOT_FOUND = "Solidary matching #%s not found";
    const SOLIDARY_SOLUTION_ROLE_REQUIRED = "A carpooler or transporter is required";
    const SOLIDARY_SOLUTION_USER_NOT_FOUND = "User #%s not found";

    // solidary message
    const SOLIDARY_MESSAGE_SOLUTION_REQUIRED= "A solution id is required";
    const SOLIDARY_MESSAGE_SOLUTION_NOT_FOUND = "Solidary solution #%s not found";
    const SOLIDARY_MESSAGE_TEXT_REQUIRED = "A text message is required";
}
