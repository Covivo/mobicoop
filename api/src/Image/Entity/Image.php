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
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Event\Entity\Event;

/**
 * An image.
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
class Image
{
    /**
     * @var int The id of this image.
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
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $name;

    /**
     * @var string The html title of the image.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("read")
     */
    private $title;
    
    /**
     * @var string The html alt of the image.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("read")
     */
    private $alt;
    
    /**
     * @var string The file name of the image.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $fileName;
    
    /**
     * @var string The encoding format of the image.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $encodingFormat;
    
    /**
     * @var string The position of the image if mulitple images are related to the same entity.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;
    
    /**
     * @var Event|null The event associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\Event\Entity\Event", inversedBy="images")
     */
    private $event;
    
    /**
     * @var ImageType The image type of the image.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Image\Entity\ImageType")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $imageType;
        
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
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(?string $title)
    {
        $this->title = $title;
    }
    
    public function getAlt(): ?string
    {
        return $this->alt;
    }
    
    public function setAlt(?string $alt)
    {
        $this->alt = $alt;
    }
    
    public function getFileName(): string
    {
        return $this->fileName;
    }
    
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }
    
    public function getEncodingFormat(): string
    {
        return $this->encodingFormat;
    }
    
    public function setEncodingFormat(string $encodingFormat)
    {
        $this->encodingFormat = $encodingFormat;
    }
    
    public function getPosition(): int
    {
        return $this->position;
    }
    
    public function setPosition(int $position): self
    {
        $this->position = $position;
        
        return $this;
    }
    
    public function getEvent(): ?Event
    {
        return $this->event;
    }
    
    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        
        return $this;
    }
    
    public function getImageType(): ImageType
    {
        return $this->imageType;
    }
    
    public function setImageType(ImageType $imageType): self
    {
        $this->imageType = $imageType;
        
        return $this;
    }
}
