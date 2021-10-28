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

namespace App\Article\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Article
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readArticle"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeArticle"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/articles",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "post"={
 *              "path"="/articles",
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "path"="/articles/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          }
 *      }
 * )
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 */
class Article
{
    const DEFAULT_ID = "999999999999";

    const CONTEXT_HOME = "home";
    const CONTEXT_INTERNAL = "internal";

    /**
     * @var int The id of the article
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readArticle"})
     */
    private $id;

    /**
     * @var string The title of the article
     *
     * @Groups({"readArticle"})
     */
    private $title;

    /**
     * @var string The description of the article
     *
     * @Groups({"readArticle"})
     */
    private $description;

    /**
     * @var string The image of the article
     *
     * @Groups({"readArticle"})
     */
    private $image;

    /**
     * @var string The code of the article iFrame if it's displayed from an external source
     *
     * @Groups({"readArticle"})
     */
    private $iFrame;
    
    /**
     * @var string The date of the post
     *
     * @Groups({"readArticle"})
     */
    private $pubDate;

    /**
     * @var string The link of the post
     *
     * @Groups({"readArticle"})
     */
    private $link;

    public function __construct(int $id=null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        } else {
            $this->id = self::DEFAULT_ID;
        }
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        
        return $this;
    }

    public function getIFrame(): ?string
    {
        return $this->iFrame;
    }
    
    public function setIFrame(?string $iFrame): self
    {
        $this->iFrame = $iFrame;
        
        return $this;
    }
    
    public function getPubDate(): ?string
    {
        return $this->pubDate;
    }

    public function setPubDate(?string $pubDate): self
    {
        $this->pubDate = $pubDate;
        
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;
        
        return $this;
    }
}
