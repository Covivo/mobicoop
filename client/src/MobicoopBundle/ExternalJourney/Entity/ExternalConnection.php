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

namespace Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An external connection
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalConnection implements ResourceInterface
{

    /**
     * @var int The id of this external connection.
     */
    private $id;

    /**
     * @var string Provider of the external journey (provider key in providers.json configuration)
     * @Groups({"post"})
     */
    private $provider;

    
    /**
     * @var int The role of the User who's sending the external connection
     * @Groups({"post"})
     */
    private $role;

    /**
     * @var string Uuid of the Carpooler targetted by the ExternalConnection (i.e. the message)
     * @Groups({"post"})
     * @Assert\NotBlank
     */
    private $carpoolerUuid;

    /**
     * @var string Uuid of the journey concerned by this ExternalConnection (i.e. the message)
     * @Groups({"post"})
     * @Assert\NotBlank
     */
    private $journeysUuid;

    /**
     * @var string Content of the ExternalConnection (i.e. the message)
     * @Groups({"post"})
     */
    private $content;


    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;
        
        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(?int $role): self
    {
        $this->role = $role;
        
        return $this;
    }

    public function getCarpoolerUuid(): ?string
    {
        return $this->carpoolerUuid;
    }

    public function setCarpoolerUuid(?string $carpoolerUuid): self
    {
        $this->carpoolerUuid = $carpoolerUuid;
        
        return $this;
    }
    
    public function getJourneysUuid(): ?string
    {
        return $this->journeysUuid;
    }

    public function setJourneysUuid(?string $journeysUuid): self
    {
        $this->journeysUuid = $journeysUuid;
        
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        
        return $this;
    }
}
