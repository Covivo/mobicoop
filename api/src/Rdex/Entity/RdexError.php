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

namespace App\Rdex\Entity;

/**
 * Rdex Error management class.
 *  
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
Class RdexError
{
    CONST ERROR_ACCESS_DENIED = "access_denied";
    CONST ERROR_ALREADY_EXISTS = "already_exists";
    CONST ERROR_INSUFFICIENT_PERMISSIONS = "insufficient_permissions";
    CONST ERROR_INTERNAL_ERROR = "internal_error";
    CONST ERROR_INVALID_INPUT = "invalid_input";
    CONST ERROR_INVALID_URI = "invalid_uri";
    CONST ERROR_INVALID_UUID = "invalid_uuid";
    CONST ERROR_MISSING_MANDATORY_FIELD = "missing_mandatory_field";
    CONST ERROR_MISSING_REQUIRED_QUERY_PARAMETER = "missing_required_query_parameter";
    CONST ERROR_NO_POST_DATA = "no_post_data";
    CONST ERROR_NO_PUT_DATA = "no_put_data";
    CONST ERROR_NOT_IMPLEMENTED = "not_implemented";
    CONST ERROR_ORIGIN_MISMATCH = "origin_mismatch";
    CONST ERROR_RESOURCE_NOT_FOUND = "resource_not_found";
    CONST ERROR_SIGNATURE_MISMATCH = "signature_mismatch";
    CONST ERROR_TIMESTAMP_TOO_SKEWED = "timestamp_too_skewed";
    CONST ERROR_TOO_LATE = "too_late";
    CONST ERROR_TOO_MANY_POST = "too_many_post";
    CONST ERROR_TOO_MANY_QUERIES = "too_many_queries";
    CONST ERROR_UNDEFINED_ERROR = "undefined_error";
    CONST ERROR_UNKNOWN_USER = "unknown_user";
    CONST ERROR_UNSUPPORTED_HTTP_VERB = "unsupported_http_verb";
    
    private CONST ERRORS = [
        self::ERROR_ACCESS_DENIED => 401,
        self::ERROR_ALREADY_EXISTS => 409,
        self::ERROR_INSUFFICIENT_PERMISSIONS => 403,
        self::ERROR_INTERNAL_ERROR => 500,
        self::ERROR_INVALID_INPUT => 400,
        self::ERROR_INVALID_URI => 400,
        self::ERROR_INVALID_UUID => 400,
        self::ERROR_MISSING_MANDATORY_FIELD => 400,
        self::ERROR_MISSING_REQUIRED_QUERY_PARAMETER => 400,
        self::ERROR_NO_POST_DATA => 400,
        self::ERROR_NO_PUT_DATA => 400,
        self::ERROR_NOT_IMPLEMENTED => 501,
        self::ERROR_ORIGIN_MISMATCH => 403,
        self::ERROR_RESOURCE_NOT_FOUND => 404,
        self::ERROR_SIGNATURE_MISMATCH => 401,
        self::ERROR_TIMESTAMP_TOO_SKEWED => 401,
        self::ERROR_TOO_LATE => 400,
        self::ERROR_TOO_MANY_POST => 429,
        self::ERROR_TOO_MANY_QUERIES => 429,
        self::ERROR_UNDEFINED_ERROR => 400,
        self::ERROR_UNKNOWN_USER => 400,
        self::ERROR_UNSUPPORTED_HTTP_VERB => 405
    ];
}