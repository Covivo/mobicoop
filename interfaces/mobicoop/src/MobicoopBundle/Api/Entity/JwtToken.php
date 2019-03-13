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

namespace Mobicoop\Bundle\MobicoopBundle\Api\Entity;

/**
 * JwtToken
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */
class JwtToken
{
    /**
     * $token.
     *
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $expiration;

    /**
     * Constructor.
     *
     * @param string    $token
     * @param \DateTime $expiration
     */
    public function __construct($token, \DateTime $expiration = null)
    {
        $this->token = $token;
        $this->expiration = $expiration;
    }

    /**
     * getToken.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->expiration) {
            return false;
        }
        return (new \DateTime()) < $this->expiration;
    }
}
