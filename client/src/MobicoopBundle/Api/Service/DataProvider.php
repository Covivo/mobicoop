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
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\JwtMiddleware;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\JwtManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth\JsonAuthStrategy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    private $client;
    private $resource;
    private $class;
    private $serializer;
    private $deserializer;
    private $format;

    /**
     * Constructor.
     *
     * @param string $uri
     * @param string $username
     * @param string $password
     * @param string $authPath
     * @param string $tokenId
     * @param Deserializer $deserializer
     */
    public function __construct(string $uri, string $username, string $password, string $authPath, string $tokenId, Deserializer $deserializer)
    {
        //Create your auth strategy
        $authStrategy = new JsonAuthStrategy(
            [
                'username' => $username,
                'password' => $password,
                'json_fields' => ['username', 'password'],
            ]
        );

        $authClient = new Client([
                'base_uri' => $uri
        ]);

        //Create the JwtManager
        $jwtManager = new JwtManager(
            $authClient,
            $authStrategy,
            $tokenId,
            [
                'token_url' => $authPath,
            ]
        );

        // Create a HandlerStack
        $stack = HandlerStack::create();

        // Add middleware
        $stack->push(new JwtMiddleware($jwtManager));

        $this->client = new Client(['handler' => $stack, 'base_uri' => $uri]);

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $encoders = array(new JsonEncoder());
        // we use our custom Object Normalizer to remove unwanted null values from the json
        $normalizers = array(new DateTimeNormalizer(), new RemoveNullObjectNormalizer($classMetadataFactory));
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->deserializer = $deserializer;

        $this->format = self::RETURN_OBJECT;
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
            $this->resource = self::pluralize((new \ReflectionClass($class))->getShortName());
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
                $clientResponse = $this->client->get($this->resource."/".$id, ['query'=>$params]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } elseif ($this->format == self::RETURN_JSON) {
                $clientResponse = $this->client->get($this->resource."/".$id, ['query'=>$params, 'headers' => ['accept' => 'application/json']]);
                $value = (string) $clientResponse->getBody();
            } else {
                $clientResponse = $this->client->get($this->resource."/".$id, ['query'=>$params]);
                $value = $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true));
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $value);
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Get special item operation
     *
     * @param int           $id                 The id of the item
     * @param string        $operation          The name of the special operation
     * @param array|null    $params             An array of parameters
     * @param bool          $reverseOperationId if true Generate an alternate uri /resource/operation/id
     *
     * @return Response The response of the operation.
     */
    public function getSpecialItem(int $id, string $operation, array $params=null, bool $reverseOperationId=false): Response
    {
        try {
            if ($this->format == self::RETURN_ARRAY) {
                $clientResponse = $this->client->get($this->resource."/".$id.'/'.$operation, ['query'=>$params]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } elseif ($this->format == self::RETURN_JSON) {
                $clientResponse = $this->client->get($this->resource."/".$id.'/'.$operation, ['query'=>$params, 'headers' => ['accept' => 'application/json']]);
                $value = (string) $clientResponse->getBody();
            } else {
                if (!$reverseOperationId) {
                    $clientResponse = $this->client->get($this->resource."/".$id.'/'.$operation, ['query'=>$params]);
                } else {
                    $clientResponse = $this->client->get($this->resource."/".$operation."/".$id, ['query'=>$params]);
                }
                $value = $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true));
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $value);
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
                $clientResponse = $this->client->get($this->resource, ['query'=>$params, 'headers' => ['accept' => 'application/json']]);
            } else {
                $clientResponse = $this->client->get($this->resource, ['query'=>$params]);
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), self::treatHydraCollection($clientResponse->getBody()));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
                $clientResponse = $this->client->get($this->resource.'/'.$operation, ['query'=>$params, 'headers' => ['accept' => 'application/json']]);
            } else {
                $clientResponse = $this->client->get($this->resource.'/'.$operation, ['query'=>$params]);
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), self::treatHydraCollection($clientResponse->getBody()));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
            $route = self::pluralize((new \ReflectionClass($subClassName))->getShortName());
        }

        try {
            if ($this->format == self::RETURN_JSON) {
                $clientResponse = $this->client->get($this->resource.'/'.$id.'/'.$route, ['query'=>$params, 'headers' => ['accept' => 'application/json']]);
            } else {
                $clientResponse = $this->client->get($this->resource.'/'.$id.'/'.$route, ['query'=>$params]);
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), self::treatHydraCollection($clientResponse->getBody(), $subClassName));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
        $op = '';
        if (!is_null($operation)) {
            $op = '/'.$operation;
        }
        try {
            if ($this->format == self::RETURN_ARRAY) {
                $clientResponse = $this->client->post($this->resource.$op, [
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true)
                ]);
                $value = json_decode((string) $clientResponse->getBody(), true);
            } elseif ($this->format == self::RETURN_JSON) {
                $clientResponse = $this->client->post($this->resource.$op, [
                        'headers' => ['accept' => 'application/json'],
                        RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true)
                ]);
                $value = (string) $clientResponse->getBody();
            } else {
                $clientResponse = $this->client->post($this->resource.$op, [
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

    public function simplePost(string $url, array $parameters = []): Response
    {
        try {
            $clientResponse = $this->client->post($url, [
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
        try {
            $uri = $this->resource."/".$operation;
            $clientResponse = $this->client->post($uri, [
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]), true),
                    'query' => $params
            ]);
            if ($clientResponse->getStatusCode() == 201) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
                        'contents'  => fopen($file->getPathname(), 'rb')
                    ];
                    $multipart[] = [
                        'name'      => self::FILE_ORIGINAL_NAME_PROPERTY,
                        'contents'  => $file->getClientOriginalName()
                    ];
                }
            }
        }
        try {
            $clientResponse = $this->client->post($this->resource, [
                'multipart' => $multipart
            ]);
            if ($clientResponse->getStatusCode() == 201) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
    public function put(ResourceInterface $object, ?array $groups=null): Response
    {
        if (is_null($groups)) {
            $groups = ['put'];
        }

        try {
            $clientResponse = $this->client->put($this->resource."/".$object->getId(), [
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]), true)
            ]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
            $clientResponse = $this->client->put($uri, [
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>$groups]), true),
                    'query' => $params
            ]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
            $clientResponse = $this->client->delete($this->resource."/".$id, ['json' => $data]);
            if ($clientResponse->getStatusCode() == 204) {
                return new Response($clientResponse->getStatusCode());
            }
        } catch (ClientException|ServerException $e) {
            return new Response($e->getCode(), self::treatHydraCollection($e->getResponse()->getBody()->getContents(), true));
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
        return Inflector::pluralize(Inflector::tableize($name));
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
            return self::replaceIris(array_filter($data, function ($value) {
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
