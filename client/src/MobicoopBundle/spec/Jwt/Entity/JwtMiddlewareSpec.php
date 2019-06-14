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

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtMiddleware;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\JwtManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\HttpBasicAuthStrategy;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * JwtMiddleware
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */


describe('JwtMiddlewareSpec', function () {
    it('should return Authorization in header and should be equal to Bearer + token value', function () {

    
        // Create a mock and queue one response.
        $authMockHandler = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'])
            ),
        ]);

        $authClient = new Client(['handler' => $authMockHandler]);
        $jwtManager = new JwtManager(
            $authClient,
            (new HttpBasicAuthStrategy(['username' => 'test', 'password' => 'test']))
        );

        $mockHandler =  new MockHandler([
            function (RequestInterface $request) {
                expect($request->hasHeader('Authorization'))->toBeTruthy();
                expect($request->getHeader('Authorization')[0])->toEqual('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9');

                return new Response(200, [], json_encode(['data' => 'pong']));
            },
        ]);
        $handler = HandlerStack::create($mockHandler);
        $handler->push(new JwtMiddleware($jwtManager));

        $client = new Client(['handler' => $handler]);
        $client->get('http://localhost:8080/users');
    });



    it('should return Authorization in header and should be equal to JWT + token value', function () {
        $baseUri = $_ENV['API_URI'];

        $authMockHandler = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'])
            ),
        ]);
        $authClient = new Client(['handler' => $authMockHandler]);
        $jwtManager = new JwtManager(
            $authClient,
            (new HttpBasicAuthStrategy(['username' => 'test', 'password' => 'test']))
        );

        $mockHandler = new MockHandler([
            function (RequestInterface $request) {
                expect($request->hasHeader('Authorization'))->toBeTruthy();
                expect($request->getHeader('Authorization')[0])->toEqual('JWT eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9');

                return new Response(200, [], json_encode(['data' => 'pong']));
            },
        ]);
        $handler = HandlerStack::create($mockHandler);
        $handler->push(new JwtMiddleware($jwtManager, 'JWT'));


        $client = new Client(['handler' => $handler]);
        $client->get($baseUri.'/users');
    });
});
