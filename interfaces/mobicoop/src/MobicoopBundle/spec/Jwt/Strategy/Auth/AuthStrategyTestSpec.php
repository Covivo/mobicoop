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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Service\Strategy\Auth;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\HttpBasicAuthStrategy;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\JsonAuthStrategy;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\QueryAuthStrategy;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\FormAuthStrategy;

/**
 * JwtMiddleware
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */


describe('AuthStrategyTest', function () {

    it('should return true and equal if the given keys is set in the array - Auth', function () {

        $authStrategy = new FormAuthStrategy(
            [
                'username' => 'admin',
                'password' => 'admin',
                'form_fields' => ['login', 'password'],
            ]
        );

        expect(array_key_exists('login', $authStrategy->getRequestOptions()['form_params']))->toBeTruthy();
        expect(array_key_exists('password', $authStrategy->getRequestOptions()['form_params']))->toBeTruthy();
        expect($authStrategy->getRequestOptions()['form_params']['login'])->toEqual('admin');
        expect($authStrategy->getRequestOptions()['form_params']['password'])->toEqual('admin');
    });

    it('should return true and equal if the given keys is set in the array - Query', function () {

        $authStrategy = new QueryAuthStrategy(
            [
                'username' => 'admin',
                'password' => 'admin',
                'query_fields' => ['username', 'password'],
            ]
        );

        expect(array_key_exists('username', $authStrategy->getRequestOptions()['query']))->toBeTruthy();
        expect(array_key_exists('password', $authStrategy->getRequestOptions()['query']))->toBeTruthy();
        expect($authStrategy->getRequestOptions()['query']['username'])->toEqual('admin');
        expect($authStrategy->getRequestOptions()['query']['password'])->toEqual('admin');
    });



    it('should contain the username and the password and should be equal to value', function () {

        $authStrategy = new HttpBasicAuthStrategy(
            [
                'username' => 'admin',
                'password' => 'password',
            ]
        );

        expect($authStrategy->getRequestOptions()['auth'][0])->toEqual('admin');
        expect($authStrategy->getRequestOptions()['auth'][1])->toEqual('password');
    });

    it('should return true and equal if the given keys is set in the array - JSON', function () {

        $authStrategy = new JsonAuthStrategy(
            [
                'username' => 'admin',
                'password' => 'admin',
                'json_fields' => ['login', 'password'],
            ]
        );

        expect(array_key_exists('login', $authStrategy->getRequestOptions()['json']))->toBeTruthy();
        expect(array_key_exists('password', $authStrategy->getRequestOptions()['json']))->toBeTruthy();
        expect($authStrategy->getRequestOptions()['json']['login'])->toEqual('admin');
        expect($authStrategy->getRequestOptions()['json']['password'])->toEqual('admin');
    });
});
