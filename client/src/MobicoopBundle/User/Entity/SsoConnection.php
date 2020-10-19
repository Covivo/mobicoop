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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A SSO Connection
 */
class SsoConnection
{
    /**
     * @var string The SSO service
     * @Groups({"post"})
     */
    private $service;

    /**
     * @var string The uri of the SSO login form
     */
    private $uri;
    
    /**
     * @var string|null The SSO service icon for the button
     */
    private $buttonIcon;
    
    public function getService(): string
    {
        return $this->service;
    }
    
    public function setService(string $service): self
    {
        $this->service = $service;
        
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

    public function getButtonIcon(): ?string
    {
        return $this->buttonIcon;
    }
    
    public function setButtonIcon(?string $buttonIcon): self
    {
        $this->buttonIcon = $buttonIcon;
        
        return $this;
    }
}
