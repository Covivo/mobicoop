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

namespace Mobicoop\Bundle\MobicoopBundle\Api\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtToken;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\AuthStrategyInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * JwtManager
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */
class JwtManager
{
    /**
     * $client Guzzle Client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * $auth Authentication Strategy.
     *
     * @var AuthStrategyInterface
     */
    protected $auth;

    /**
     * $options.
     *
     * @var array
     */
    protected $options;

    /**
     * $token.
     *
     * @var JwtToken
     */
    protected $token;

    /**
     * $cache Cache system.
     *
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param ClientInterface       $client
     * @param AuthStrategyInterface $auth
     * @param array                 $options
     */
    public function __construct(
        ClientInterface $client,
        AuthStrategyInterface $auth,
        array $options = []
    ) {
        $this->client = $client;
        $this->auth = $auth;
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'token_url' => '/token',
            'timeout' => 1,
            'token_key' => 'token',
            'expire_key' => 'expires_in',
        ]);
        $resolver->setRequired(['token_url', 'timeout']);
        $this->options = $resolver->resolve($options);

        // search for a token in the cache
        $this->cache = new FilesystemAdapter();
        $token = $this->cache->getItem('mobicoop.jwt.token');
        if ($token->isHit()) {
            $this->token = $token->get();
        }

    }

    /**
     * getToken.
     *
     * @return JwtToken
     */
    public function getJwtToken()
    {
        if ($this->token && $this->token->isValid()) {
            return $this->token;
        }
        // no token found or token invalid => clear cache
        $this->cache->deleteItem('mobicoop.jwt.token');
        $url = $this->options['token_url'];
        $requestOptions = array_merge(
            $this->getDefaultHeaders(),
            $this->auth->getRequestOptions()
        );
        $response = $this->client->request('POST', $url, $requestOptions);
        $body = json_decode($response->getBody(), true);
        $expiresIn = isset($body[$this->options['expire_key']]) ? $body[$this->options['expire_key']] : null;
        if ($expiresIn) {
            $expiration = new \DateTime('now + ' . $expiresIn . ' seconds', new \DateTimeZone('UTC'));
        } elseif (count($jwtParts = explode('.', $body[$this->options['token_key']])) === 3
            && is_array($payload = json_decode(base64_decode($jwtParts[1]), true))
            // https://tools.ietf.org/html/rfc7519.html#section-4.1.4
            && array_key_exists('exp', $payload)
        ) {
            // Manually process the payload part to avoid having to drag in a new library
            $expiration = new \DateTime('@' . $payload['exp'], new \DateTimeZone('UTC'));
        } else {
            $expiration = null;
        }
        $this->token = new JwtToken($body[$this->options['token_key']], $expiration);

        // save the token in the cache
        $token = $this->cache->getItem('mobicoop.jwt.token');
        $token->set($this->token);
        $this->cache->save($token);

        return $this->token;
    }
    /**
     * getHeaders. Return defaults header.
     *
     * @return array
     */
    private function getDefaultHeaders()
    {
        return [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'timeout' => $this->options['timeout'],
            ],
        ];
    }
}
