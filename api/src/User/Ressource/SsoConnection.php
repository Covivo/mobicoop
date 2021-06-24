<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\User\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Entity\User;

/**
 * A SSO Connection
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSSOConnection"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSSOConnection"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SsoConnection
{
    const DEFAULT_ID = 999999999999;

    const RETURN_URL = "user/sso/login";
    const LOGIN_BUTTON_ICON = "/images/sso/{serviceId}-sso-login.png";

    /**
     * @var int The id of this Block
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSSOConnection"})
     */
    private $id;

    /**
     * @var string The Name of the SSO service
     * @Groups({"readSSOConnection"})
     */
    private $name;
    
    /**
     * @var string The uri of the SSO login form
     * @Groups({"readSSOConnection"})
     */
    private $uri;

    /**
     * @var string The client id
     */
    private $clientId;
    
    /**
     * @var string|null The client secret
     */
    private $clientSecret;
    
    /**
     * @var string|null The return url after the connection
     */
    private $returnUrl;

    /**
     * @var string The SSO service name
     * @Groups({"readSSOConnection"})
     */
    private $service;

    /**
     * @var string The SSO provider internal name
     * @Groups({"readSSOConnection"})
     */
    private $ssoProvider;

    /**
     * @var string|null The SSO service icon for the button
     * @Groups({"readSSOConnection"})
     */
    private $buttonIcon;
    
    public function __construct(string $id=null)
    {
        (is_null($id)) ? $this->id = self::DEFAULT_ID : $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
    
    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        
        return $this;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }
    
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        
        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }
    
    public function setClientSecret(?string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;
        
        return $this;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }
    
    public function setReturnUrl(?string $returnUrl): self
    {
        $this->returnUrl = $returnUrl;
        
        return $this;
    }

    public function getService(): string
    {
        return $this->service;
    }
    
    public function setService(string $service): self
    {
        $this->service = $service;
        
        return $this;
    }

    public function getSsoProvider(): ?string
    {
        return $this->ssoProvider;
    }
    
    public function setSsoProvider(?string $ssoProvider): self
    {
        $this->ssoProvider = $ssoProvider;
        
        return $this;
    }

    public function getButtonIcon(): ?string
    {
        return str_replace('{serviceId}', $this->id, self::LOGIN_BUTTON_ICON);
    }
    
    public function setButtonIcon(?string $buttonIcon): self
    {
        $this->buttonIcon = $buttonIcon;
        
        return $this;
    }
}
