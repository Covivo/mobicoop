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

namespace App\I18n\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A translate.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"I18n"}
 *              }
 *          }
 *      }
 * )
 */
class Translate
{
   
    /**
     * @var int The id of the relation between source and language
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","read","write"})
     */
    private $id;

    /**
     * @var Language The language concerned
     *
     * @ORM\ManyToOne(targetEntity="\App\I18n\Entity\Language", inversedBy="translates")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"aRead","read","write"})
     * @MaxDepth(1)
     */
    private $language;

    /**
     * @var Source The source concerned
     *
     * @ORM\ManyToOne(targetEntity="\App\I18n\Entity\Source", inversedBy="translates")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"aRead","read","write"})
     * @MaxDepth(1)
     */
    private $source;

    /**
     * @var string The text of the source.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","read","write"})
     */
    private $text;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        
        return $this;
    }
}
