<?php

namespace App\ExternalJourney\Entity;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * An arrival.
 *
 * @ApiResource(
 *      collectionOperations={"get"}
 * )
 */
class ExternalJourney
{
    /**
    * @ApiProperty(identifier=true)
    */
    private $id;

    public function __construct(){
    }

    public function getid(){
      return $this->id;
    }

    public function setid($id){
      return $this->id = $id;
    }
}