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
 */

namespace App\Article\Entity;

/**
 * A RSS element.
 *
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 */
class RssElement
{
    /**
     * @var int The id of the article
     */
    private $id;

    /**
     * @var string The title of the article
     */
    private $title;

    /**
     * @var string The description of the article
     */
    private $description;

    /**
     * @var string The image of the article
     */
    private $image;

    /**
     * @var Iframe Iframes of the article
     */
    private $iframe;

    /**
     * @var string The date of the post
     */
    private $pubDate;

    /**
     * @var string The link of the post
     */
    private $link;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
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

    public function getIframe(): ?Iframe
    {
        return $this->iframe;
    }

    public function setIframe(?Iframe $iframe): self
    {
        $this->iframe = $iframe;

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
