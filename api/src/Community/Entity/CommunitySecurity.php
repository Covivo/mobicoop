<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Community\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * The securization of a community.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class CommunitySecurity
{
    const FILETYPE_STRING = 0;
    const FILETYPE_DATE = 1;

    /**
     * @var int The id of this community security.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
        
    /**
     * @var Community The community.
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community", inversedBy="communitySecurities")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $community;

    /**
     * @var string The filename of the community security.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $filename;

    /**
     * @var string Name of the first field to check.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $field1Name;

    /**
     * @var int Type of the first field to check.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $field1Type;
    
    /**
     * @var string Name of the second field to check.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $field2Name;

    /**
     * @var int Type of the second field to check.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $field2Type;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;
        
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
    
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function getField1Name(): string
    {
        return $this->field1Name;
    }
    
    public function setField1Name(string $field1Name)
    {
        $this->field1Name = $field1Name;
    }

    public function getField1Type(): ?int
    {
        return $this->field1Type;
    }

    public function setField1Type(int $field1Type)
    {
        $this->field1Type = $field1Type;
    }

    public function getField2Name(): string
    {
        return $this->field2Name;
    }
    
    public function setField2Name(string $field2Name)
    {
        $this->field2Name = $field2Name;
    }

    public function getField2Type(): ?int
    {
        return $this->field2Type;
    }

    public function setField2Type(int $field2Type)
    {
        $this->field2Type = $field2Type;
    }
}