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
class RdexError
{
    const ERROR_ACCESS_DENIED = "access_denied";
    const ERROR_ALREADY_EXISTS = "already_exists";
    const ERROR_INSUFFICIENT_PERMISSIONS = "insufficient_permissions";
    const ERROR_INTERNAL_ERROR = "internal_error";
    const ERROR_INVALID_INPUT = "invalid_input";
    const ERROR_INVALID_URI = "invalid_uri";
    const ERROR_INVALID_UUID = "invalid_uuid";
    const ERROR_MISSING_MANDATORY_FIELD = "missing_mandatory_field";
    const ERROR_MISSING_REQUIRED_QUERY_PARAMETER = "missing_required_query_parameter";
    const ERROR_NO_POST_DATA = "no_post_data";
    const ERROR_NO_PUT_DATA = "no_put_data";
    const ERROR_NOT_IMPLEMENTED = "not_implemented";
    const ERROR_ORIGIN_MISMATCH = "origin_mismatch";
    const ERROR_RESOURCE_NOT_FOUND = "resource_not_found";
    const ERROR_SIGNATURE_MISMATCH = "signature_mismatch";
    const ERROR_TIMESTAMP_TOO_SKEWED = "timestamp_too_skewed";
    const ERROR_TOO_LATE = "too_late";
    const ERROR_TOO_MANY_POST = "too_many_post";
    const ERROR_TOO_MANY_QUERIES = "too_many_queries";
    const ERROR_UNDEFINED_ERROR = "undefined_error";
    const ERROR_UNKNOWN_USER = "unknown_user";
    const ERROR_UNSUPPORTED_HTTP_VERB = "unsupported_http_verb";
    const ERROR_MISSING_CONFIG = "config_file_missing";
    
    const ERRORS = [
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
        self::ERROR_UNSUPPORTED_HTTP_VERB => 405,
        self::ERROR_MISSING_CONFIG => 500
    ];
    
    /**
     * @var string The error name.
     */
    private $name;
 
    /**
     * @var int The http error code.
     */
    private $code;
    
    /**
     * @var string Message for debugging purpose.
     */
    private $message_debug;
    
    /**
     * @var string Message for the end-user.
     */
    private $message_user;
    
    /**
     * @var string The field in question.
     */
    private $field;
    
    public function __construct(string $field=null, string $error, string $message_debug=null, string $message_user=null)
    {
        $this->setName($error);
        $this->setField($field);
        $this->setMessageDebug($message_debug);
        $this->setMessageUser($message_user);
        $this->setCode(self::ERRORS[$error]);
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return number
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessageDebug()
    {
        return $this->message_debug;
    }

    /**
     * @return string
     */
    public function getMessageUser()
    {
        return $this->message_user;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param number $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $message_debug
     */
    public function setMessageDebug($message_debug)
    {
        $this->message_debug = $message_debug;
    }

    /**
     * @param string $message_user
     */
    public function setMessageUser($message_user)
    {
        $this->message_user = $message_user;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
}
