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

namespace App\Image\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A thumbnail type (max size, format...).
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class ThumbnailType
{
    /**
     * @var int The id of this thumbnail type.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the image.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups("read")
     */
    private $name;

    /**
     * @var int The maximum size of the generated thumbnail image.
     *
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $size;
    
    /**
     * @var string Force to the encoding format of the generated thumbnail image.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("read")
     */
    private $encodingFormat;
    
    public function __construct($id=null)
    {
        $this->id = $id;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function getSize(): int
    {
        return $this->size;
    }
    
    public function setSize(int $size): self
    {
        $this->size = $size;
        
        return $this;
    }
    
    public function getEncodingFormat(): ?string
    {
        return $this->encodingFormat;
    }
    
    public function setEncodingFormat(?string $encodingFormat)
    {
        $this->encodingFormat = $encodingFormat;
    }
}
