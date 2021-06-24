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

namespace App\ExternalJourney\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An external carpool journey provider.
 * For now providers are configured in a json config file, but maybe it should be in the database.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sofiane Belaribi <sofiane.belaribi@covivo.eu>
 */
class ExternalJourneyProvider
{
    /**
    * @var int $id  The id of the provider (not useful yet but needed for api)
    * @ApiProperty(identifier=true)
    */
    private $id;

    /**
     * @var string $name        The name of the provider
     * @Groups("read")
     */
    private $name;

    /**
     * @var string $url         The url of the provider
     */
    private $url;

    /**
     * @var string $resource    The name of the resource of the provider
     */
    private $resource;

    /**
     * @var string $apiKey      The api key of the provider
     */
    private $apiKey;

    /**
     * @var string $privateKey  The private key of the provider
     */
    private $privateKey;

    public function __construct()
    {
        $this->id = 1;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name): self
    {
        $this->name = $name;
        
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    public function setUrl(?string $url): self
    {
        $this->url = $url;
        
        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }
    
    public function setResource(?string $resource): self
    {
        $this->resource = $resource;
        
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }
    
    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;
        
        return $this;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }
    
    public function setPrivateKey(?string $privateKey): self
    {
        $this->privateKey = $privateKey;
        
        return $this;
    }
}
