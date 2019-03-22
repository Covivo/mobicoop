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

use Mobicoop\Bundle\MobicoopBundle\Api\Service\JwtManager;
use Psr\Http\Message\RequestInterface;

/**
 * JwtMiddleware
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */
class JwtMiddleware
{
    /**
     * $JwtManager.
     *
     * @var JwtManager
     */
    protected $jwtManager;
    /**
     * The Authorization Header Type (defaults to Bearer)
     *
     * @var string
     */
    protected $authorizationHeaderType;
    /**
     * Constructor.
     *
     * @param JwtManager $jwtManager
     * @param string $authorizationHeaderType
     */
    public function __construct(JwtManager $jwtManager, $authorizationHeaderType = 'Bearer')
    {
        $this->jwtManager = $jwtManager;
        $this->authorizationHeaderType = $authorizationHeaderType;
    }
    /**
     * Called when the middleware is handled by the client.
     *
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(callable $handler)
    {
        $manager = $this->jwtManager;
        return function (
            RequestInterface $request,
            array $options
        ) use (
            $handler,
            $manager
        ) {
            $token = $manager->getJwtToken()->getToken();
            return $handler($request->withHeader(
                'Authorization',
                sprintf('%s %s', $this->authorizationHeaderType, $token)
            ), $options);
        };
    }
}
