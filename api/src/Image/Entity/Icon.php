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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\RelayPoint\Entity\RelayPointType;

/**
 * An icon
 *
 * @ORM\Entity()
 * @ORM\EntityListeners({"App\Image\EntityListener\IconListener"})
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *      },
 *      collectionOperations={
 *          "ADMIN_get"={
 *              "path"="/admin/icons",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('image_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "get"={
 *             "security"="is_granted('image_list',object)",
 *             "swagger_context" = {
 *                  "tags"={"Pictures"}
 *             }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('image_read',object)",
 *             "swagger_context" = {
 *                  "tags"={"Pictures"}
 *             }
 *          }
 *      }
 * )
 */
class Icon
{
    const DEFAULT_ICON_ID = 1; // Default Icon
    
    /**
     * @var int The id of this icon.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","read","readRelayPoint"})
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the icon.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","read","readRelayPoint"})
     */
    private $name;

    /**
     * @var string The filename of the icon.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","readRelayPoint"})
     */
    private $fileName;

    /**
     * @var string The url of the icon.
     *
     * @Groups({"aRead","read","readRelayPoint"})
     */
    private $url;

    /**
     * @var ArrayCollection|null The relayPointTypes associate to the icon.
     *
     * @ORM\OneToMany(targetEntity="\App\RelayPoint\Entity\RelayPointType", mappedBy="icon")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $relayPointTypes;

    /**
     * @var Icon|null Linked icon for the private related item.
     *
     * @ORM\OneToOne(targetEntity="\App\Image\Entity\Icon", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","readRelayPoint"})
     * @MaxDepth(1)
     */
    private $privateIconLinked;

    /**
    * @var string|null The private icon name
    * @Groups("aRead")
    */
    private $privateName;

    /**
     * @var string|null The private icon url
     * @Groups("aRead")
     */
    private $privateUrl;

    public function __construct()
    {
        $this->relayPointTypes = new ArrayCollection();
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

    public function getFileName(): string
    {
        return $this->fileName;
    }
    
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getRelayPointTypes()
    {
        return $this->relayPointTypes->getValues();
    }

    public function addRelaypointType(RelayPointType $relayPointType): self
    {
        if (!$this->relayPointTypes->contains($relayPointType)) {
            $this->relayPointTypes[] = $relayPointType;
            $relayPointType->setIcon($this);
        }

        return $this;
    }

    public function removeRelaypointType(RelayPointType $relayPointType): self
    {
        if ($this->relayPointTypes->contains($relayPointType)) {
            $this->relayPointTypes->removeElement($relayPointType);
            // set the owning side to null (unless already changed)
            if ($relayPointType->getIcon() === $this) {
                $relayPointType->setIcon(null);
            }
        }

        return $this;
    }

    public function getPrivateIconLinked(): ?self
    {
        return $this->privateIconLinked;
    }

    public function setPrivateIconLinked(?self $privateIconLinked): self
    {
        $this->privateIconLinked = $privateIconLinked;
        
        // set (or unset) the owning side of the relation if necessary
        $newPrivateIconLinked = $privateIconLinked === null ? null : $this;
        if ($newPrivateIconLinked !== $privateIconLinked->getPrivateIconLinked()) {
            $privateIconLinked->setPrivateIconLinked($newPrivateIconLinked);
        }
        
        return $this;
    }

    public function getPrivateName(): ?string
    {
        if ($this->getPrivateIconLinked()) {
            return $this->getPrivateIconLinked()->getName();
        }
        return null;
    }

    public function getPrivateUrl(): ?string
    {
        if ($this->getPrivateIconLinked()) {
            return $this->getPrivateIconLinked()->getUrl();
        }
        return null;
    }
}
