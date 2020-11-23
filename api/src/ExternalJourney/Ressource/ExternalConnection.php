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

namespace App\ExternalJourney\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An external connection (i.e. sending a message) to an ExternalJourneyProvider
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readExternalConnection"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeExternalConnection"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)"
 *          },
 *          "post"
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('review_read',object)"
 *          },
 *          "put"={
 *              "security"="is_granted('reject',object)"
 *          },
 *          "delete"={
 *              "security"="is_granted('reject',object)"
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalConnection
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int Id of the ExternalConnection
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readExternalConnection"})
    */
    private $id;

    /**
     * @var String Operator making the ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $operator;

    /**
     * @var String  Origin site of the ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $origin;

    /**
     * @var string Uuid of the Carpooler targetted by the ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     * @Assert\NotBlank
     */
    private $carpoolerUuid;

    /**
     * @var String Uuid of the journey concerned by this ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     * @Assert\NotBlank
     */
    private $journeysUuid;

    /**
     * @var String Details of the ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $details;

    public function __construct(int $id = null)
    {
        $this->id = self::DEFAULT_ID;
        if (!is_null($id)) {
            $this->id = $id;
        }
    }
        
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getOperator(): String
    {
        return $this->operator;
    }

    public function setOperator(String $operator): self
    {
        $this->operator = $operator;
        
        return $this;
    }

    public function getOrigin(): String
    {
        return $this->origin;
    }

    public function setOrigin(String $origin): self
    {
        $this->origin = $origin;
        
        return $this;
    }
       
    public function getCarpoolerUuid(): String
    {
        return $this->carpoolerUuid;
    }

    public function setCarpoolerUuid(String $carpoolerUuid): self
    {
        $this->carpoolerUuid = $carpoolerUuid;
        
        return $this;
    }
    
    public function getJourneysUuid(): String
    {
        return $this->journeysUuid;
    }

    public function setJourneysUuid(String $journeysUuid): self
    {
        $this->journeysUuid = $journeysUuid;
        
        return $this;
    }

    public function getDetails(): String
    {
        return $this->details;
    }

    public function setDetails(String $details): self
    {
        $this->details = $details;
        
        return $this;
    }
}
