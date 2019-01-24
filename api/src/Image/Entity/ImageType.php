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
 * An image type (image for a user, an event...).
 *
 * @ORM\Entity(repositoryClass="App\Image\Repository\ImageTypeRepository")
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class ImageType
{
    const TYPE_USER = 1;
    const TYPE_EVENT = 2;
    
    /**
     * @var int The id of this image type.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the image type.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $name;
    
    /**
     * @var string The folder of the image type.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $folder;
    
    /**
     * @var ThumbnailType[]|null The thumbnail types to generate for the image type.
     *
     * @ORM\ManyToMany(targetEntity="\App\Image\Entity\ThumbnailType")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $thumbnailTypes;
    
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
    
    public function getFolder(): string
    {
        return $this->folder;
    }
    
    public function setFolder(string $folder)
    {
        $this->folder = $folder;
    }
    
    /**
     * @return Collection|ThumbnailType[]
     */
    public function getThumbnailTypes(): Collection
    {
        return $this->thumbnailTypes;
    }
    
    public function addThumbnailType(ThumbnailType $thumbnailType): self
    {
        if (!$this->thumbnailTypes->contains($thumbnailType)) {
            $this->thumbnailTypes[] = $thumbnailType;
        }
        
        return $this;
    }
    
    public function removeThumbnailType(ThumbnailType $thumbnailType): self
    {
        if ($this->thumbnailTypes->contains($thumbnailType)) {
            $this->thumbnailTypes->removeElement($thumbnailType);
        }
        
        return $this;
    }
}
