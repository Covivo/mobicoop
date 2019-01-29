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

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Response;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\HydraView;

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
        
    private $client;
    private $resource;
    private $class;
    private $serializer;
    private $deserializer;
    
    public function __construct($uri, Deserializer $deserializer)
    {
        $this->client = new Client([
                'base_uri' => $uri
        ]);
        
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        
        $encoders = array(new JsonEncoder());
        // we use our custom Object Normalizer to remove unwanted null values from the json
        $normalizers = array(new DateTimeNormalizer(), new RemoveNullObjectNormalizer($classMetadataFactory));
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->deserializer = $deserializer;
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
     * Get item operation
     *
     * @param int       $id         The id of the item
     * @param Boolean   $asArray    Return the result as an array instead of an object
     *
     * @return Response The response of the operation.
     */
    public function getItem(int $id, bool $asArray = false): Response
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
            $clientResponse = $this->client->get($this->resource."/".$id);
            if ($asArray) {
                $value = json_decode((string) $clientResponse->getBody(), true);
            } else {
                $value = $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true));
            }
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $value);
            }
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
            $clientResponse = $this->client->get($this->resource, ['query'=>$params]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), self::treatHydraCollection($clientResponse->getBody()));
            }
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
     *
     * @return Response The response of the operation.
     */
    public function getSubCollection(int $id, string $subClassName, string $subClassRoute=null, array $params=null): Response
    {
        // @todo : send the params to the request in the json body of the request

        $route = $subClassRoute;
        if (is_null($route)) {
            $route = self::pluralize((new \ReflectionClass($subClassName))->getShortName());
        }

        try {
            $clientResponse = $this->client->get($this->resource.'/'.$id.'/'.$route);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), self::treatHydraCollection($clientResponse->getBody(), $subClassName));
            }
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }
    
    /**
     * Post collection operation
     *
     * @param Resource $object An object representing the resource to post
     *
     * @return Response The response of the operation.
     */
    public function post(Resource $object): Response
    {
        try {
            $clientResponse = $this->client->post($this->resource, [
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['post']]), true)
            ]);
            if ($clientResponse->getStatusCode() == 201) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }
    
    /**
     * Put item operation
     *
     * @param object $object An object representing the resource to put
     *
     * @return Response The response of the operation.
     */
    public function put(Resource $object): Response
    {
        try {
            $clientResponse = $this->client->put($this->resource."/".$object->getId(), [
                    RequestOptions::JSON => json_decode($this->serializer->serialize($object, self::SERIALIZER_ENCODER, ['groups'=>['put']]), true)
            ]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $this->deserializer->deserialize($this->class, json_decode((string) $clientResponse->getBody(), true)));
            }
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
     * @return Response The response of the operation.
     */
    public function delete(int $id): Response
    {
        try {
            $clientResponse = $this->client->delete($this->resource."/".$id);
            if ($clientResponse->getStatusCode() == 204) {
                return new Response($clientResponse->getStatusCode());
            }
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
                $hydraView->setId($data['hydra:view']['@type']);
            }
            if (isset($data['hydra:view']['hydra:first'])) {
                $hydraView->setId($data['hydra:view']['hydra:first']);
            }
            if (isset($data['hydra:view']['hydra:last'])) {
                $hydraView->setId($data['hydra:view']['hydra:last']);
            }
            if (isset($data['hydra:view']['hydra:next'])) {
                $hydraView->setId($data['hydra:view']['hydra:next']);
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
        $data = parent::normalize($object, $format, $context);
        
        return self::replaceIris(array_filter($data, function ($value) {
            return (null !== $value) && (!(empty($value) && is_array($value)));
        }));
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
