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
namespace Mobicoop\Bundle\MobicoopBundle\Spec\Service\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtToken;

/**
 * JwtToken
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */

describe('JwtTokenSpec', function () {
    it('should not be valid if expiration in the past', function () {
        $token = new JwtToken('foo', new \DateTime('now - 5 minutes'));
        expect($token->isValid())->toBeFalsy();
    });

    it('should not be valid if expiration is now', function () {
        $token = new JwtToken('foo', new \DateTime('now'));
        expect($token->isValid())->toBeFalsy();
    });

    it('should be valid if expiration is in the future', function () {
        $token = new JwtToken('foo', new \DateTime('now + 5 minutes'));
        expect($token->isValid())->toBeTruthy();
    });
});
