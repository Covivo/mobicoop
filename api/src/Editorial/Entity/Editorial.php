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

namespace App\Editorial\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Image\Entity\Image;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * An editorial
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"App\Editorial\EntityListener\EditorialListener"})
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readEditorial"}, "enable_max_depth"="true"},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={
 *          "ADMIN_get"={
 *              "path"="/admin/editorials",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_editorial_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/editorials",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_editorial_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('editorial_read',object)",
 *              "normalization_context"={"groups"={"readEditorial"}},
 *              "swagger_context" = {
 *                  "summary"="Get THE ONLY ONE activated editorial",
 *                  "tags"={"Editorials"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/editorials/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_editorial_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/editorials/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_editorial_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/editorials/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_editorial_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title", "status"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"id":"exact", "status":"exact","title":"partial"})
 */
class Editorial
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;
    /**
     * @var int The id of this EDITORIAL.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","readEditorial"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string The title of the editorial.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readEditorial"})
     */
    private $title;

    /**
     * @var string The text of the editorial.
     *
     * @ORM\Column(type="string", length=512)
     * @Groups({"aRead","aWrite","readEditorial"})
     */
    private $text;

    /**
     * @var string Label of the button of the editorial content
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readEditorial"})
     */
    private $label;

    /**
     * @var string The url linked to the editorial content
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readEditorial"})
     */
    private $link;

    /**
     * @var int The status of the editorial (active/inactive).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite"})
     */
    private $status;

    /**
     * @var ArrayCollection The images of the editorial.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="editorial", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups("readEditorial")
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
    * @var \DateTimeInterface Creation date of the editorial.
    *
    * @ORM\Column(type="datetime")
    * @Groups({"aRead"})
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the editorial.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"aRead"})
     */
    private $updatedDate;

    /**
     * @var string|null The editorial image
     * @Groups("aRead")
     */
    private $image;

    /**
     * @var string|null The editorial avatar
     * @Groups("aRead")
     */
    private $avatar;


    public function __construct($id=null)
    {
        $this->id = $id;
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text)
    {
        $this->text = $text;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link)
    {
        $this->link = $link;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getImages()
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setEditorial($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getEditorial() === $this) {
                $image->setEditorial(null);
            }
        }

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function getImage(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_800'])) {
            return $this->getImages()[0]->getVersions()['square_800'];
        }
        return null;
    }

    public function getAvatar(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_250'])) {
            return $this->getImages()[0]->getVersions()['square_250'];
        }
        return null;
    }

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
