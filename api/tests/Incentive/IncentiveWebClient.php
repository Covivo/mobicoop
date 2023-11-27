<?php

namespace App\Tests\Incentive;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversNothing
 */
abstract class IncentiveWebClient extends WebTestCase
{
    protected const ADMIN_USER = [
        'username' => 'corentin.keroual@mobicoop.org',
        'pwd' => 'Corentin123',
    ];

    protected const USER = [
        'username' => 'umberto.picaldi@mobicoop.org',
        'pwd' => 'Umberto123',
    ];

    protected $_client;

    protected $_response;

    protected function createBaseClient()
    {
        $this->_client = static::createClient();

        return $this->_client;
    }

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'user', $password = 'password')
    {
        $this->_client = static::createClient();
        $this->_client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => $password,
            ])
        );

        $data = json_decode($this->_client->getResponse()->getContent(), true);

        $this->_client->setServerParameters([
            'HTTP_Authorization' => sprintf('Bearer %s', $data['token']),
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        return $this->_client;
    }

    protected function requestUnauthorized(string $endpoint)
    {
        $this->createBaseClient();
        $this->_client->request('GET', $endpoint);

        $this->setResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    protected function requestToken(string $endpoint, array $user = null)
    {
        $user = !is_null($user) ? $user : self::ADMIN_USER;

        $this->_client = $this->createAuthenticatedClient($user['username'], $user['pwd']);
        $this->_client->request('GET', $endpoint, ['Authorization']);

        $this->setResponse();
    }

    protected function setResponse()
    {
        $this->_response = json_decode($this->_client->getResponse()->getContent());

        return $this->_response;
    }

    protected function setEndpointParameters(string $endpoint, array $params): string
    {
        foreach ($params as $key => $value) {
            $key = '{'.$key.'}';
            $endpoint = str_replace($key, $value, $endpoint);
        }

        return $endpoint;
    }
}
