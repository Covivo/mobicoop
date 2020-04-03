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

namespace App\Solidary\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary contact
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get","post"
 *
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 */
class SolidaryContact
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $id;

    /**
    * @var SolidarySolution The solidary solution this contact is for
    * @Groups({"readSolidary","writeSolidary"})
    * @MaxDepth(1)
    */
    private $solidarySolution;

    /**
    * @var string The content (usually text) message of this contact
    * @Assert\NotBlank
    * @Groups({"readSolidary","writeSolidary"})
    * @MaxDepth(1)
    */
    private $content;

    /**
    * @var ArrayCollection List of the Medium of this contact
    * @Groups({"readSolidary"})
    */
    private $media;


    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->media = new ArrayCollection();
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

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }
    
    public function setSolidary(SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;
        
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
    
    public function setContent(string $content): self
    {
        $this->content = $content;
        
        return $this;
    }

    public function getMedia(): ?ArrayCollection
    {
        return $this->media;
    }
    
    public function setMedia(ArrayCollection $media): self
    {
        $this->media = $media;
        
        return $this;
    }
}
