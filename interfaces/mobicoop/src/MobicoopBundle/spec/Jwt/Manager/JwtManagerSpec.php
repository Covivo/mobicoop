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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Service\Manager;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtToken;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\JwtManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\QueryAuthStrategy;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * JwtManager
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */

describe('JwtManagerSpec', function () {

    it('getToken', function () {

        $mockHandler = new MockHandler([
            function (RequestInterface $request) {
                expect($request->hasHeader('timeout'))->toBeTruthy();
                expect($request->getHeaderLine('timeout'))->toEqual(3);

                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['token' => '1453720507'])
                );
            },
        ]);
        $handler = HandlerStack::create($mockHandler);
        $authClient = new Client([
            'handler' => $handler,
        ]);
        $authStrategy = new QueryAuthStrategy(['username' => 'admin', 'password' => 'admin']);
        $jwtManager = new JwtManager(
            $authClient,
            $authStrategy,
            ['token_url' => '/api/token', 'timeout' => 3]
        );
        $token = $jwtManager->getJwtToken();
        expect($token)->toBeAnInstanceOf(JwtToken::class);
        expect($token->getToken())->toEqual('1453720507');
    });


    it('getToken should get token with token key option', function () {
        $mockHandler = new MockHandler([
            function (RequestInterface $request) {
                expect($request->hasHeader('timeout'))->toBeTruthy();
                expect($request->getHeaderLine('timeout'))->toEqual(3);

                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['tokenkey' => '1453720507'])
                );
            },
        ]);
        $handler = HandlerStack::create($mockHandler);
        $authClient = new Client([
            'handler' => $handler,
        ]);
        $authStrategy = new QueryAuthStrategy(['username' => 'admin', 'password' => 'admin']);
        $jwtManager = new JwtManager(
            $authClient,
            $authStrategy,
            ['token_url' => '/api/token', 'timeout' => 3, 'token_key' => 'tokenkey']
        );
        $token = $jwtManager->getJwtToken();
        expect($token)->toBeAnInstanceOf(JwtToken::class);
        expect($token->getToken())->toEqual('1453720507');
    });

    it('getToken should get new token if cached token is not valid', function () {
        $mockHandler = new MockHandler(
            [
                function (RequestInterface $request) {
                    expect($request->hasHeader('timeout'))->toBeTruthy();
                    expect($request->getHeaderLine('timeout'))->toEqual(3);

                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode(['token' => '1453720507'])
                    );
                },

                function (RequestInterface $request) {
                    expect($request->hasHeader('timeout'))->toBeTruthy();
                    expect($request->getHeaderLine('timeout'))->toEqual(3);

                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode(['token' => 'foo123'])
                    );
                },
            ]
        );
        $handler = HandlerStack::create($mockHandler);
        $authClient = new Client([
            'handler' => $handler,
        ]);
        $authStrategy = new QueryAuthStrategy(['username' => 'admin', 'password' => 'admin']);
        $jwtManager = new JwtManager(
            $authClient,
            $authStrategy,
            ['token_url' => '/api/token', 'timeout' => 3]
        );
        $token = $jwtManager->getJwtToken();
        expect($token)->toBeAnInstanceOf(JwtToken::class);
        expect($token->getToken())->toEqual('1453720507');

        $token = $jwtManager->getJwtToken();
        expect($token)->toBeAnInstanceOf(JwtToken::class);
        expect($token->getToken())->toEqual('foo123');
    });


    it('getToken should use the cached token if it is valid', function () {
        $mockHandler = new MockHandler(
            [
                function (RequestInterface $request) {
                    expect($request->hasHeader('timeout'))->toBeTruthy();
                    expect($request->getHeaderLine('timeout'))->toEqual(3);
                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode(['token' => '1453720507', 'expires_in' => 3600])
                    );
                },
                function (RequestInterface $request) {
                    expect($request->hasHeader('timeout'))->toBeTruthy();
                    expect($request->getHeaderLine('timeout'))->toEqual(3);
                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode(['token' => 'foo123'])
                    );
                },
            ]
        );
        $handler = HandlerStack::create($mockHandler);
        $authClient = new Client([
            'handler' => $handler,
        ]);
        $authStrategy = new QueryAuthStrategy(['username' => 'admin', 'password' => 'admin']);
        $jwtManager = new JwtManager(
            $authClient,
            $authStrategy,
            ['token_url' => '/api/token', 'timeout' => 3]
        );
        $token = $jwtManager->getJwtToken();
        expect($token)->toBeAnInstanceOf(JwtToken::class);
        expect($token->getToken())->toEqual('1453720507');
        $token = $jwtManager->getJwtToken();
        expect($token)->toBeAnInstanceOf(JwtToken::class);
        expect($token->getToken())->toEqual('1453720507');
    });


    // describe('testGetTokenShouldUseTheCachedTokenIfItIsValidBasedOnExpField', function () {
    //     it('getToken should use the cached token if it is valid based on Exp Field', function () { });
    //     $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'
    //         . '.eyJleHAiOiIzMjUwMzY4MDAwMCJ9'
    //         . '.k4YJmJooaa9B4pAM_U8Pi-4ss6RdKFtj9iQqLIAndVA';
    //     $mockHandler = new MockHandler(
    //         [
    //             function (RequestInterface $request) use ($jwtToken) {
    //                 expect($request->hasHeader('timeout'))->toBeTruthy();
    //                 expect($request->getHeaderLine('timeout'))->toEqual(3);
    //                 return new Response(
    //                     200,
    //                     ['Content-Type' => 'application/json'],
    //                     json_encode(['token' => $jwtToken])
    //                 );
    //             },
    //             function (RequestInterface $request) {
    //                 expect($request->hasHeader('timeout'))->toBeTruthy();
    //                 expect($request->getHeaderLine('timeout'))->toEqual(3);
    //                 return new Response(
    //                     200,
    //                     ['Content-Type' => 'application/json'],
    //                     json_encode(['token' => uniqid('token', true)])
    //                 );
    //             },
    //         ]
    //     );
    //     $handler = HandlerStack::create($mockHandler);
    //     $authClient = new Client([
    //         'handler' => $handler,
    //     ]);
    //     $authStrategy = new QueryAuthStrategy(['username' => 'admin', 'password' => 'admin']);
    //     $jwtManager = new JwtManager(
    //         $authClient,
    //         $authStrategy,
    //         ['token_url' => '/api/token', 'timeout' => 3]
    //     );
    //     $token = $jwtManager->getJwtToken();
    //     expect($token)->toBeAnInstanceOf(JwtToken::class);
    //     expect($token->getToken())->toEqual($jwtToken);

    //     $token = $jwtManager->getJwtToken();
    //     expect($token)->toBeAnInstanceOf(JwtToken::class);
    //     expect($token->getToken())->toEqual($jwtToken);
    // });
});
