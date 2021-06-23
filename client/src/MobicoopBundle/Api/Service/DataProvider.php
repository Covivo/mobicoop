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

use GuzzleHttp\Exception\ClientException;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Response;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\HydraView;

use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Trace;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\HttpFoundation\JsonResponse;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtMiddleware;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtToken;
use Mobicoop\Bundle\MobicoopBundle\Api\Exception\ApiTokenException;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\JwtManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\JsonAuthStrategy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Data provider service.
 * Uses an API to retrieve/send data.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class DataProvider
{
    const SERIALIZER_ENCODER = 'json';

    // possible file properties and associated getter, used for multipart/form-data
    const FILE_PROPERTIES = [
        'eventFile' => 'getEventFile',
        'userFile'  => 'getUserFile',
        'communityFile' => 'getCommunityFile',
        'relayPointFile' => 'getRelayPointFile',
        'relayPointTypeFile' => 'getRelayPointTypeFile',
        'file' => 'getFile'
    ];

    // original name property for file-based entities
    const FILE_ORIGINAL_NAME_PROPERTY = 'originalName';

    // accepted return format
    const RETURN_OBJECT = 1;
    const RETURN_ARRAY = 2;
    const RETURN_JSON = 3;

    private $uri;
    private $username;
    private $usernameDelegate;
    private $password;
    private $emailToken;
    private $passwordToken;
    private $ssoId;
    private $ssoProvider;
    private $authPath;
    private $loginPath;
    private $refreshPath;
    private $loginTokenPath;
    private $loginSsoPath;
    private $tokenId;
    private $authLoginPath;
    private $session;
    private $baseSiteUri;

    /**
     * @var JWTToken $jwtToken
     */
    private $jwtToken;
    private $refreshToken;

    private $client;
    private $resource;
    private $class;
    private $serializer;
    private $deserializer;
    private $format;
    private $cache;
    private $inflector;

    private $request;

    /**
     * Constructor.
     *
     * @param string $uri                   The api uri
     * @param string $username              The default api username
     * @param string $password              The default api password
     * @param string $emailToken            The email token for authentification
     * @param string $passwordToken         The reset password token for authentification
     * @param string $authPath              The api path for default authentication
     * @param string $loginPath             The api path for user authentication
     * @param string $loginDelegatePath     The api path for user delegation authentication
     * @param string $loginTokenPath        The api path for user authentication with only validate token
     * @param string $loginSsoPath          The api path for user authentication by sso
     * @param string $tokenId               The token id
     * @param Deserializer $deserializer    The deserializer
     * @param SessionInterface  $session    The session
     */
    public function __construct(string $uri, string $username, string $emailToken = null, string $passwordToken = null, string $password, string $authPath, string $loginPath, string $loginDelegatePath, string $refreshPath, string $loginTokenPath, string $loginSsoPath, string $tokenId, Deserializer $deserializer, SessionInterface $session, RequestStack $requestStack)
    {
        $this->uri = $uri;
        $this->username = $username;
        $this->password = $password;
        $this->emailToken = $emailToken;
        $this->passwordToken = $passwordToken;
        $this->authPath = $authPath;
        $this->loginPath = $loginPath;
        $this->loginDelegatePath = $loginDelegatePath;
        $this->refreshPath = $refreshPath;
        $this->loginTokenPath = $loginTokenPath;
        $this->loginSsoPath = $loginSsoPath;
        $this->tokenId = $tokenId;
        $this->authLoginPath = $authPath;
        $this->session = $session;
        $this->private = false;
        $this->cache = new FilesystemAdapter();
        $this->inflector = InflectorFactory::create()->build();
        $this->request = $requestStack->getCurrentRequest();

        // use the following for debugging token related problems !
        // $this->cache->deleteItem($this->tokenId.'.jwt.token');
        // $this->session->remove('apiToken');
        // $this->session->remove('apiRefreshToken');

        // check an existing jwt token
        if ($apiToken = $this->session->get('apiToken')) {
            // there's an api token in session => private connection, it's a real human user
            if ($apiToken->isValid()) {
                // the token is still valid, we use it !
                $this->jwtToken = $apiToken;
                $this->private = true;
            } else {
                // the token is invalid, we remove it from session
                $this->session->remove('apiToken');
                // is there a refresh token ?
                if ($refreshToken = $this->session->get('apiRefreshToken')) {
                    // there's a refresh token in session
                    $this->refreshToken = $refreshToken;
                    $this->private = true;
                }
            }
        } else {
            // check if there's a global api token in system cache => public connection (app)
            $cachedToken = $this->cache->getItem($this->tokenId.'.jwt.token');
            if ($cachedToken->isHit()) {
                /**
                 * @var JWTToken $jwtToken
                 */
                $jwtToken = $cachedToken->get();
                if ($jwtToken && $jwtToken->isValid()) {
                    $this->jwtToken = $jwtToken;
                } else {
                    // clear cache
                    $this->cache->deleteItem($this->tokenId.'.jwt.token');
                }
            }
            // check if there's a global api refresh token in system cache
            $cachedRefreshToken = $this->cache->getItem($this->tokenId.'.jwt.refresh.token');
            if ($cachedRefreshToken->isHit()) {
                $this->refreshToken = $cachedRefreshToken->get();
            }
        }
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $encoders = array(new JsonEncoder());
        // we use our custom Object Normalizer to remove unwanted null values from the json
        $normalizers = array(new DateTimeNormalizer(), new RemoveNullObjectNormalizer($classMetadataFactory));
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->deserializer = $deserializer;
        $this->format = self::RETURN_OBJECT;

        $this->client = new Client(['base_uri' => $this->uri]);
    }

    /**
     * Set the username (for user authentication)
     *
     * @param string $username  The username
     * @return void
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * Set the delegate username (for delegate user authentication)
     *
     * @param string $username  The delegated username
     * @return void
     */
    public function setUsernameDelegate(string $usernameDelegate)
    {
        $this->usernameDelegate = $usernameDelegate;
    }

    /**
     * Set the email token (for user authentication with email token)
     *
     * @param string $emailToken  The token
     * @return void
     */
    public function setEmailToken(string $emailToken)
    {
        $this->emailToken = $emailToken;
    }

    /**
     * Set the reset password token (for user authentication with reset password token)
     *
     * @param string $passwordToken  The token
     * @return void
     */
    public function setPasswordToken(string $passwordToken)
    {
        $this->passwordToken = $passwordToken;
    }

    public function setSsoId(string $ssoId)
    {
        $this->ssoId = $ssoId;
    }

    public function setSsoProvider(string $ssoProvider)
    {
        $this->ssoProvider = $ssoProvider;
    }

    public function setBaseSiteUri(string $baseSiteUri)
    {
        $this->baseSiteUri = $baseSiteUri;
    }

    /**
     * Set the password (for user authentication)
     *
     * @param string $password  The password
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Set the authentication to private (for user authentication : change the api login path and the token storage system)
     *
     * @param boolean $private  True to set to private
     * @return void
     */
    public function setPrivate(bool $private)
    {
        $this->private = $private;
        if ($private) {
            if ($this->emailToken || $this->passwordToken) {
                $this->authLoginPath = $this->loginTokenPath;
            } elseif ($this->ssoId && $this->ssoProvider) {
                $this->authLoginPath = $this->loginSsoPath;
            } elseif ($this->usernameDelegate) {
                $this->authLoginPath = $this->loginDelegatePath;
            } else {
                $this->authLoginPath = $this->loginPath;
            }
            $this->jwtToken = null;
            $this->refreshToken = null;
        } else {
            $this->authLoginPath = $this->authPath;
        }
    }

    /**
     * Get the Client headers, including the token bearer.
     * Automatically call for a token if not present.
     *
     * @param array $headers The headers to add
     * @return void
     */
    private function getHeaders(array $headers=[])
    {
        $this->createToken();

        // automatically add the bearer token
        $headers['Authorization'] = 'Bearer ' . $this->jwtToken->getToken();

        // Add the locale
        $headers['X-LOCALE'] = $this->request->headers->get("x-locale");

        // additional headers
        foreach ($headers as $header) {
            switch ($header) {
                case 'json':
                    $headers['accept'] = 'application/json';
                    break;
            }
        }

        return $headers;
    }

    public function createToken()
    {
        if (is_null($this->jwtToken)) {
            $tokens = $this->getJwtToken();
            if (is_null($tokens) || !is_array($tokens)) {
                throw new ApiTokenException("Bad credentials");
            }

            if (!isset($tokens['token']) || !isset($tokens['refreshToken'])) {
                throw new ApiTokenException("Empty API or refresh token.");
            }

            $expiration = null;

            // decode token to get the expiration
            if (count($jwtParts = explode('.', $tokens['token'])) === 3
                && is_array($payload = json_decode(base64_decode($jwtParts[1]), true))
                // https://tools.ietf.org/html/rfc7519.html#section-4.1.4
                && array_key_exists('exp', $payload)
            ) {
                // Manually process the payload part to avoid having to drag in a new library
                $expiration = new \DateTime('@' . $payload['exp'], new \DateTimeZone('UTC'));
            }

            $this->jwtToken = new JWTToken($tokens['token'], $expiration);
            $this->refreshToken = $tokens['refreshToken'];

            if ($this->private) {
                // private request, store in session
                $this->session->set('apiToken', $this->jwtToken);
                $this->session->set('apiRefreshToken', $this->refreshToken);
            } else {
                // public request, store in system cache
                $cachedToken = $this->cache->getItem($this->tokenId.'.jwt.token');
                $cachedToken->set($this->jwtToken);
                $cachedRefreshToken = $this->cache->getItem($this->tokenId.'.jwt.refresh.token');
                $cachedRefreshToken->set($this->refreshToken);
                $this->cache->save($cachedToken);
                $this->cache->save($cachedRefreshToken);
            }
        }
    }

    /**
     * Call for an api jwt token
     *
     * @return array|null  The token and refreshToken retrieved
     */
    private function getJwtToken()
    {
        $value = null;

        // is there a refresh token ?
        if ($this->refreshToken) {
            try {
                $clientResponse = $this->client->post($this->refreshPath, [
                            'headers' => ['accept' => 'application/json'],
                            RequestOptions::JSON => [
                                "refreshToken" => $this->refreshToken
                            ]
                    ]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } catch (ServerException $e) {
                throw new ApiTokenException("Server error : unable to get an API token from refresh.");
            } catch (ClientException $e) {
                // todo : check the exception to test the different cases
                // invalid credentials
                $this->cache->deleteItem($this->tokenId.'.jwt.refresh.token');
            }
        }
        // no refresh token or refresh token expired
        if (is_null($value)) {
            // We have a username and emailToken
            if (!is_null($this->emailToken)) {
                try {
                    $clientResponse = $this->client->post($this->authLoginPath, [
                                  'headers' => ['accept' => 'application/json'],
                                  RequestOptions::JSON => [
                                      "email" => $this->username,
                                      "emailToken" => $this->emailToken
                                  ]
                          ]);
                    $value = json_decode((string) $clientResponse->getBody(), true);
                } catch (ServerException $e) {
                    throw new ApiTokenException("Unable to get an API token.");
                } catch (ClientException $e) {
                    if ($e->getCode() == '401') {
                        return new JsonResponse('bad-credentials-api');
                    }
                    throw new ApiTokenException("Unable to get an API token.");
                }
                // We have a reset password token
            } elseif (!is_null($this->passwordToken)) {
                try {
                    $clientResponse = $this->client->post($this->authLoginPath, [
                                'headers' => ['accept' => 'application/json'],
                                RequestOptions::JSON => [
                                    "passwordToken" => $this->passwordToken
                                ]
                        ]);
                    $value = json_decode((string) $clientResponse->getBody(), true);
                } catch (ServerException $e) {
                    throw new ApiTokenException("Unable to get an API token.");
                } catch (ClientException $e) {
                    //Wrong credentials
                    if ($e->getCode() == '401') {
                        return new JsonResponse('bad-credentials-api');
                    }
                    throw new ApiTokenException("Unable to get an API token.");
                }
            } elseif (!is_null($this->ssoId) && !is_null($this->ssoProvider)) {
                try {
                    $clientResponse = $this->client->post($this->authLoginPath, [
                                'headers' => ['accept' => 'application/json'],
                                RequestOptions::JSON => [
                                    "ssoId" => $this->ssoId,
                                    "ssoProvider" => $this->ssoProvider,
                                    "baseSiteUri" => $this->baseSiteUri
                                ]
                        ]);
                    $value = json_decode((string) $clientResponse->getBody(), true);
                } catch (ServerException $e) {
                    throw new ApiTokenException("Unable to get an API token.");
                } catch (ClientException $e) {
                    //Wrong credentials
                    if ($e->getCode() == '401') {
                        return new JsonResponse('bad-credentials-api');
                    }
                    throw new ApiTokenException("Unable to get an API token.");
                }
            } elseif (!is_null($this->usernameDelegate) && !is_null($this->username) && !is_null($this->password)) {
                // we have a username, usernameDelegate and password
                try {
                    $clientResponse = $this->client->post($this->authLoginPath, [
                                'headers' => ['accept' => 'application/json'],
                                RequestOptions::JSON => [
                                    "username" => $this->username,
                                    "username_delegate" => $this->usernameDelegate,
                                    "password" => $this->password
                                ]
                        ]);
                    $value = json_decode((string) $clientResponse->getBody(), true);
                } catch (ServerException $e) {
                    throw new ApiTokenException("Unable to get an API token.");
                } catch (ClientException $e) {
                    //Wrong credentials
                    if ($e->getCode() == '401') {
                        return new JsonResponse('bad-credentials-api');
                    }
                    throw new ApiTokenException("Unable to get an API token.");
                }
            } else {
                // we have a username and password
                try {
                    $clientResponse = $this->client->post($this->authLoginPath, [
                                'headers' => ['accept' => 'application/json'],
                                RequestOptions::JSON => [
                                    "username" => $this->username,
                                    "password" => $this->password
                                ]
                        ]);
                    $value = json_decode((string) $clientResponse->getBody(), true);
                } catch (ServerException $e) {
                    throw new ApiTokenException("Unable to get an API token.");
                } catch (ClientException $e) {
                    //Wrong credentials
                    if ($e->getCode() == '401') {
                        return new JsonResponse('bad-credentials-api');
                    }
                    throw new ApiTokenException("Unable to get an API token.");
                }
            }
        }
        return $value;
    }

    /**
     * @param string        $class      The name of the class
     * @param string|null   $resource   The resource name if different than the pluralized class name
     * @throws \ReflectionException
     */
    public function setClass(string $class, $resource=null)
    {
        $this->class = $class;
        if ($resource != null) {
            $this->resource = $resource;
        } else {
            $this->resource = $this->pluralize((new \ReflectionClass($class))->getShortName());
        }
    }

    /**
     * Set format
     *
     * @param integer $format
     * @return void
     */
    public function setFormat(int $format)
    {
        $this->format = $format;
    }

    /**
     * Get item operation
     *
     * @param int           $id         The id of the item
     * @param array|null    $params     An array of parameters
     *
     * @return Response The response of the operation.
     */
    public function getItem(int $id, array $params = null): Response
    {
        /*
         * deserialization of nested array of objects doesn't work...
         * only the root object deserialization works...
         * see https://medium.com/@rebolon/the-symfony-serializer-a-great-but-complex-component-fbc09baa65a0
         */
        /*
         return $this->serializer->deserialize((string) $response->getBody(), $this->class, self::SERIALIZER_ENCODER);
         */
        try {
            if ($this->format == self::RETURN_ARRAY) {
                $headers = $this->getHeaders();
                $clientResponse = $this->client->get($this->resource."/".$id, ['query'=>$params, 'headers' => $headers]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } elseif ($this->format == self::RETURN_JSON) {
                $headers = $this->getHeaders(['json']);
                $clientResponse = $this->client->get($this->resource."/".$id, ['query'=>$params, 'headers' => $headers]);
                $value = (string) $clientResponse->getBody();
            } else {
                $headers = $this->getHeaders();
                $clientResponse = $this->client->get($this->resource."/".$id, ['query'=>$params, 'headers' => $headers]);
                $value = $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true));
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $value);
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Get special item operation
     *
     * @param mixed         $id                 The id of the item (usually an int, can be a string in rare cases !)
     * @param string        $operation          The name of the special operation
     * @param array|null    $params             An array of parameters
     * @param bool          $reverseOperationId if true Generate an alternate uri /resource/operation/id
     *
     * @return Response The response of the operation.
     */
    public function getSpecialItem($id, string $operation, array $params=null, bool $reverseOperationId=false): Response
    {
        try {
            if ($this->format == self::RETURN_ARRAY) {
                $headers = $this->getHeaders();
                $clientResponse = $this->client->get($this->resource."/".$id.'/'.$operation, ['query'=>$params, 'headers' => $headers]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } elseif ($this->format == self::RETURN_JSON) {
                $headers = $this->getHeaders(['json']);
                $clientResponse = $this->client->get($this->resource."/".$id.'/'.$operation, ['query'=>$params, 'headers' => $headers]);
                $value = (string) $clientResponse->getBody();
            } else {
                $headers = $this->getHeaders();
                if (!$reverseOperationId) {
                    $clientResponse = $this->client->get($this->resource."/".$id.'/'.$operation, ['query'=>$params, 'headers' => $headers]);
                } else {
                    $clientResponse = $this->client->get($this->resource."/".$operation."/".$id, ['query'=>$params, 'headers' => $headers]);
                }
                $value = $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true));
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $value);
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Get collection operation
     *
     * @param array|null    $params         An array of parameters
     *
     * @return Response The response of the operation.
     */
    public function getCollection(array $params=null): Response
    {
        try {
            if ($this->format == self::RETURN_JSON) {
                $headers = $this->getHeaders(['json']);
                // var_dump($this->resource, ['query'=>$params, 'headers' => $headers]);die;

                $clientResponse = $this->client->get($this->resource, ['query'=>$params, 'headers' => $headers]);
            } else {
                $headers = $this->getHeaders();
                //var_dump($this->resource, ['query'=>$params, 'headers' => $headers]);die;

                $clientResponse = $this->client->get($this->resource, ['query'=>$params, 'headers' => $headers]);
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->treatHydraCollection($clientResponse->getBody()));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Get special collection operation
     *
     * @param string        $operation      The name of the special operation
     * @param array|null    $params         An array of parameters
     *
     * @return Response The response of the operation.
     */
    public function getSpecialCollection(string $operation, ?array $params=null): Response
    {
        try {
            if ($this->format == self::RETURN_JSON) {
                $headers = $this->getHeaders(['json']);
                $clientResponse = $this->client->get($this->resource.'/'.$operation, ['query'=>$params, 'headers' => $headers]);
            } else {
                // var_dump($this->resource.'/'.$operation, ['query'=>$params]);
                $headers = $this->getHeaders();
                if ($headers == "bad-credentials-api") {
                    return new Response(401, 'bad-credentials-api');
                }
                $clientResponse = $this->client->get($this->resource.'/'.$operation, ['query'=>$params, 'headers' => $headers]);
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->treatHydraCollection($clientResponse->getBody()));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Get sub collection operation
     *
     * @param int           $id             The id of the item
     * @param string        $subClassName   The classname of the subresource
     * @param string        $subClassRoute  The class route of the subresource (used for custom routes, if not provided the route will be the subClassName pluralized)
     * @param array|null    $params         An array of parameters
     * @return Response The response of the operation.
     * @throws \ReflectionException
     */
    public function getSubCollection(int $id, string $subClassName, ?string $subClassRoute=null, ?array $params=null): Response
    {
        $route = $subClassRoute;
        if (is_null($route)) {
            $route = $this->pluralize((new \ReflectionClass($subClassName))->getShortName());
        }

        try {
            if ($this->format == self::RETURN_JSON) {
                $headers = $this->getHeaders(['json']);
                // var_dump($this->resource.'/'.$id.'/'.$route, ['query'=>$params, 'headers' => $headers]);die;

                $clientResponse = $this->client->get($this->resource.'/'.$id.'/'.$route, ['query'=>$params, 'headers' => $headers]);
            } else {
                $headers = $this->getHeaders();
                // var_dump($this->resource.'/'.$id.'/'.$route, ['query'=>$params, 'headers' => $headers]);die;

                $clientResponse = $this->client->get($this->resource.'/'.$id.'/'.$route, ['query'=>$params, 'headers' => $headers]);
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->treatHydraCollection($clientResponse->getBody(), $subClassName));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Post collection operation
     *
     * @param ResourceInterface $object An object representing the resource to post
     *
     * @return Response The response of the operation.
     */
    public function post(ResourceInterface $object, ?string $operation = null): Response
    {
        // var_dump($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]));
        // exit;
        $op = '';
        if (!is_null($operation)) {
            $op = '/'.$operation;
        }
        try {
            if ($this->format == self::RETURN_ARRAY) {
                $headers = $this->getHeaders();
                $clientResponse = $this->client->post($this->resource.$op, [
                    'headers' => $headers,
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true)
                ]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } elseif ($this->format == self::RETURN_JSON) {
                $headers = $this->getHeaders(['json']);
                $clientResponse = $this->client->post($this->resource.$op, [
                        'headers' => $headers,
                        RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true)
                ]);
                $value = (string) $clientResponse->getBody();
            } else {
                $headers = $this->getHeaders();
                $clientResponse = $this->client->post($this->resource.$op, [
                    'headers' => $headers,
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true)
                ]);
                $value = $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true));
            }
            if ($clientResponse->getStatusCode() == 201) {
                return new Response($clientResponse->getStatusCode(), $value);
            }
        } catch (ServerException $e) {
            return new Response($e->getCode(), $e->getMessage());
        } catch (ClientException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }
        return new Response();
    }

    /**
     * Post on a given url
     *
     * @param string $url       The url to post on
     * @param array $parameters The parameters
     * @return Response         The response
     */
    public function simplePost(string $url, array $parameters = []): Response
    {
        try {
            $headers = $this->getHeaders();
            $clientResponse = $this->client->post($url, [
                'headers' => $headers,
                RequestOptions::JSON => $parameters,
            ]);
            $value = (string) $clientResponse->getBody();
            return new Response($clientResponse->getStatusCode(), $value);
        } catch (ServerException $e) {
            return new Response($e->getCode(), $e->getMessage());
        } catch (ClientException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Post item with special operation
     *
     * @param ResourceInterface $object An object representing the resource to put
     *
     * @return Response The response of the operation.
     */
    public function postSpecial(ResourceInterface $object, ?array $groups=null, ?string $operation, ?array $params=null, bool $reverseOperationId=false): Response
    {
        if (is_null($groups)) {
            $groups = ['post'];
        }

        // var_dump($this->resource."/".$operation);
        // var_dump($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]));die;

        try {
            $uri = $this->resource."/".$operation;
            $headers = $this->getHeaders();
            $clientResponse = $this->client->post($uri, [
                    'headers' => $headers,
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]), true),
                    'query' => $params
            ]);
            if ($clientResponse->getStatusCode() == 201) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Post collection operation with multipart/form-data
     *
     * @param ResourceInterface $object An object representing the resource to post
     *
     * @return Response The response of the operation.
     */
    public function postMultiPart(ResourceInterface $object): Response
    {
        $multipart = [];
        // we serialize the serializable properties
        $data = json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true);
        foreach ($data as $key=>$value) {
            $multipart[] = [
                'name'      => $key,
                'contents'  => $value
            ];
        }
        // we check for other possible file properties
        foreach (self::FILE_PROPERTIES as $property=>$getter) {
            if (method_exists($object, $getter)) {
                $file = $object->$getter();

                if ($file instanceof UploadedFile) {
                    $multipart[] = [
                        'name'      => $property,
                        'filename' => $file->getClientOriginalName(),
                        'contents'  => fopen($file->getPathname(), 'rb')
                    ];
                    $multipart[] = [
                        'name'      => self::FILE_ORIGINAL_NAME_PROPERTY,
                        'contents'  => $file->getClientOriginalName()
                    ];
                }
            }
        }
        // var_dump($multipart);die;
        try {
            $headers = $this->getHeaders();
            $clientResponse = $this->client->post($this->resource, [
                'headers' => $headers,
                'multipart' => $multipart
            ]);
            // var_dump(json_decode((string) $clientResponse->getBody(), true));die;
            if ($clientResponse->getStatusCode() == 201) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Put item operation
     *
     * @param ResourceInterface $object An object representing the resource to put
     *
     * @return Response The response of the operation.
     */
    public function put(ResourceInterface $object, ?array $groups=null, ?array $params = null): Response
    {
        if (is_null($groups)) {
            $groups = ['put'];
        }
        // var_dump("put");
        // var_dump($this->resource."/".$object->getId());
        // var_dump($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]));die;
        try {
            $headers = $this->getHeaders();
            $clientResponse = $this->client->put($this->resource."/".$object->getId(), [
                    'headers' => $headers,
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]), true),
                    'query' => $params
            ]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Put item with special operation
     *
     * @param ResourceInterface $object An object representing the resource to put
     *
     * @return Response The response of the operation.
     */
    public function putSpecial(ResourceInterface $object, ?array $groups=null, ?string $operation, ?array $params=null, bool $reverseOperationId=false): Response
    {
        if (is_null($groups)) {
            $groups = ['put'];
        }

        try {
            if (!$reverseOperationId) {
                $uri = $this->resource."/".$object->getId()."/".$operation;
            } else {
                $uri = $this->resource."/".$operation."/".$object->getId();
            }
            // var_dump("put special");
            // var_dump($uri);
            // var_dump($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]));die;

            $headers = $this->getHeaders();
            $clientResponse = $this->client->put($uri, [
                    'headers' => $headers,
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]), true),
                    'query' => $params
            ]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Delete item operation
     *
     * @param int $id The id of the object representing the resource to delete
     *
     * @param array|null $data
     * @return Response The response of the operation.
     */
    public function delete(int $id, ?array $data=null): Response
    {
        try {
            $headers = $this->getHeaders();
            $clientResponse = $this->client->delete($this->resource."/".$id, ['headers' => $headers, 'json' => $data]);
            if ($clientResponse->getStatusCode() == 204) {
                return new Response($clientResponse->getStatusCode());
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), $this->treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    private function treatHydraCollection($data, $class=null)
    {
        // if $class is defined, it's because our request concerns a subresource
        if (!$class) {
            $class = $this->class;
        }
        if ($this->format != self::RETURN_OBJECT) {
            return json_decode((string) $data, true);
        }

        // $data comes from a GuzzleHttp request; it's a json hydra collection so when need to parse the json to an array
        $data = json_decode($data, true);

        $hydra = new Hydra();
        if (isset($data['@context'])) {
            $hydra->setContext($data['@context']);
        }
        if (isset($data['@id'])) {
            $hydra->setId($data['@id']);
        }
        if (isset($data['@type'])) {
            $hydra->setType($data['@type']);
        }
        if (isset($data['hydra:title'])) {
            $hydra->setTitle($data['hydra:title']);
        }
        if (isset($data['hydra:description'])) {
            $hydra->setDescription($data['hydra:description']);
        }
        if (isset($data['trace'])) {
            $hydra->setTraces(Trace::load($data['trace']));
        }
        if (isset($data['hydra:totalItems'])) {
            $hydra->setTotalItems($data['hydra:totalItems']);
        }
        if (isset($data['hydra:member'])) {
            /*
             * deserialization of nested array of objects doesn't work...
             * only the root object deserialization works...
             * see https://medium.com/@rebolon/the-symfony-serializer-a-great-but-complex-component-fbc09baa65a0
             */

            /*$members = [];
            foreach ($data["hydra:member"] as $key=>$value) {
                $object = $this->serializer->deserialize(json_encode($value), $this->class, self::SERIALIZER_ENCODER);
                // we had the @id => iri
                if (isset($value['@id']) && method_exists($object, 'setIri')) $object->setIri($value['@id']);
                $members[] = $object;
            }
            $hydra->setMember($members);*/

            $members = [];
            foreach ($data["hydra:member"] as $key=>$value) {
                $members[] = $this->deserializer->deserialize($class, $value);
            }
            $hydra->setMember($members);
        }
        if (isset($data['hydra:view'])) {
            $hydraView = new HydraView();
            if (isset($data['hydra:view']['@id'])) {
                $hydraView->setId($data['hydra:view']['@id']);
            }
            if (isset($data['hydra:view']['@type'])) {
                $hydraView->setType($data['hydra:view']['@type']);
            }
            if (isset($data['hydra:view']['hydra:first'])) {
                $hydraView->setFirst($data['hydra:view']['hydra:first']);
            }
            if (isset($data['hydra:view']['hydra:last'])) {
                $hydraView->setLast($data['hydra:view']['hydra:last']);
            }
            if (isset($data['hydra:view']['hydra:next'])) {
                $hydraView->setNext($data['hydra:view']['hydra:next']);
            }
            $hydra->setView($hydraView);
        }
        return $hydra;
    }

    private function pluralize(string $name): string
    {
        return $this->inflector->pluralize($this->inflector->tableize($name));
    }

    public function getToken()
    {
        $this->createToken();
        return $this->jwtToken->getToken();
    }
}

/**
 * This class permits to remove null values or empty arrays when normalizing.
 * It also permits to replace object values by their IRI if set.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class RemoveNullObjectNormalizer extends ObjectNormalizer
{
    public function normalize($object, $format = null, array $context = [])
    {
        // circular references are now handled by a dedicated class in Api\Serializer

        $data = parent::normalize($object, $format, $context);
        if (is_array($data)) {
            return $this->replaceIris(array_filter($data, function ($value) {
                return (null !== $value) && (!(empty($value) && is_array($value)));
            }));
        }
        return $data;
    }

    /**
     * This function replaces each value in an array by its IRI value if IRI key exists.
     * (recursive function)
     *
     * eg:
     *
     * [
     *      "id"    => 1,
     *      "user"  => [
     *          "id"    => 2,
     *          "name"  => "John",
     *          "iri"   => "/users/2"
     *      ]
     * ]
     *
     *  will be replaced by :

     * [
     *      "id"    => 1,
     *      "user"  => "/users/2"
     * ]
     *
     */
    private function replaceIris(array $array): array
    {
        $replacedArray = [];
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                if (isset($value['iri']) && !is_null($value['iri'])) {
                    $replacedArray[$key] = $value['iri'];
                } else {
                    $replacedArray[$key] = self::replaceIris($value);
                }
            } else {
                $replacedArray[$key] = $value;
            }
        }
        return $replacedArray;
    }
}
