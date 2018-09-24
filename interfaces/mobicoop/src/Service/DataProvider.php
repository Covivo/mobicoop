<?php

namespace App\Service;

use GuzzleHttp\Client;
use App\Entity\Hydra;
use App\Entity\HydraView;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Inflector\Inflector;

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
    
    public function __construct($uri,Deserializer $deserializer)
    {
        $this->client = new Client([
                'base_uri' => $uri
        ]);
        
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->deserializer = $deserializer;
    }
    
    public function setClass(string $class)
    {
        $this->class = $class;
        $this->resource = self::pluralize((new \ReflectionClass($class))->getShortName());
    }
    
    /**
     * Get item operation
     *
     * @param String    $id         The id of the item
     * @param Boolean   $asArray    Return the result as an array instead of an object
     *
     * @return object|array|null The item found or null if not found.
     */
    public function getItem(string $id, bool $asArray = false)
    {
        $response = $this->client->get($this->resource."/".$id);
        
        if ($asArray) return json_decode((string) $response->getBody(),true);
        
        /*
         * deserialization of nested array of objects doesn't work...
         * only the root object deserialization works...
         * see https://medium.com/@rebolon/the-symfony-serializer-a-great-but-complex-component-fbc09baa65a0
         */
        /* 
        return $this->serializer->deserialize((string) $response->getBody(), $this->class, self::SERIALIZER_ENCODER);
        */
        
       return $this->deserializer->deserialize($this->class, json_decode((string) $response->getBody(),true));
        
    }
    
    /**
     * Get collection operation
     *
     * @param array|null $params An array of parameters
     *
     * @return Hydra The hydra collection found or null if not found.
     */
    public function getCollection(array $params=null): ?Hydra
    {
        // @todo : send the params to the request in the json body of the request
        $response = $this->client->get($this->resource);
        return self::treatHydraCollection($response->getBody());
    }
    
    private function treatHydraCollection($data) 
    {   
        // $data comes from a GuzzleHttp request; it's a json hydra collection so when need to parse the json to an array
        $data = json_decode($data,true);
        $hydra = new Hydra();
        if (isset($data['@context'])) $hydra->setContext($data['@context']);
        if (isset($data['@id'])) $hydra->setId($data['@id']);
        if (isset($data['@type'])) $hydra->setType($data['@type']);
        if (isset($data['hydra:totalItems'])) $hydra->setTotalItems($data['hydra:totalItems']);
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
                $members[] = $this->deserializer->deserialize($this->class,$value);
            }
            $hydra->setMember($members);            
        }
        if (isset($data['hydra:view'])) {
            $hydraView = new HydraView();
            if (isset($data['hydra:view']['@id'])) $hydraView->setId($data['hydra:view']['@id']);
            if (isset($data['hydra:view']['@type'])) $hydraView->setId($data['hydra:view']['@type']);
            if (isset($data['hydra:view']['hydra:first'])) $hydraView->setId($data['hydra:view']['hydra:first']);
            if (isset($data['hydra:view']['hydra:last'])) $hydraView->setId($data['hydra:view']['hydra:last']);
            if (isset($data['hydra:view']['hydra:next'])) $hydraView->setId($data['hydra:view']['hydra:next']);
            $hydra->setView($hydraView);
        }
        return $hydra;
    }
    
    private function pluralize(string $name): string
    {
        return Inflector::pluralize(Inflector::tableize($name));
    }
    
}