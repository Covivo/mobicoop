<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Communication\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Contact Type
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readContactType"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeContactType"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ContactType
{
    const DEFAULT_ID = 999999999999;

    const TYPE_SUPPORT = "technicalIssues";

    /**
     * @var int The id of the contact type
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readContactType"})
     */
    private $id;

    /**
     * @var string|null Demand for this contact type
     * @Assert\NotBlank
     * @Groups({"readContactType"})
     */
    private $demand;

    /**
     * @var string|null Object code use to define the object of the email (ref to the yaml translation files)
     * @Assert\NotBlank
     * @Groups({"readContactType"})
     */
    private $objectCode;

    /**
     * @var array|null Receiving emails for this contact type
     * @Assert\NotBlank
     */
    private $to;

    /**
     * @var array|null Receiving emails (carbon copy) for this contact type
     */
    private $cc;

    /**
     * @var array|null Receiving emails (blind carbon copy) for this contact type
     */
    private $bcc;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getDemand(): ?string
    {
        return $this->demand;
    }

    public function setDemand(string $demand): self
    {
        $this->demand = $demand;
        
        return $this;
    }

    public function getObjectCode(): ?string
    {
        return $this->objectCode;
    }

    public function setObjectCode(string $objectCode): self
    {
        $this->objectCode = $objectCode;
        
        return $this;
    }

    public function getTo(): ?array
    {
        return $this->to;
    }

    public function setTo(array $to): self
    {
        $this->to = $to;
        
        return $this;
    }

    public function getCc(): ?array
    {
        return $this->cc;
    }

    public function setCc(array $cc): self
    {
        $this->cc = $cc;
        
        return $this;
    }

    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;
        
        return $this;
    }
}
