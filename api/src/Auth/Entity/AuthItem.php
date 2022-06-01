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

namespace App\Auth\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An authorization item = a role or an item.
 *
 * @ORM\Entity
 * @UniqueEntity("name")
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"authRead"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"authWrite"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(NumericFilter::class, properties={"type"})
 * @ApiFilter(OrderFilter::class, properties={"id", "type", "name"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 */
class AuthItem
{
    public const TYPE_ROLE = 2;
    public const TYPE_ITEM = 1;

    public const SPECIAL_ITEMS = ["manage"];

    public const ROLE_SUPER_ADMIN = 1;
    public const ROLE_ADMIN = 2;
    public const ROLE_USER_REGISTERED_FULL = 3;
    public const ROLE_USER_REGISTERED_MINIMAL = 4;
    public const ROLE_USER = 5;
    public const ROLE_MASS_MATCH = 6;
    public const ROLE_COMMUNITY_MANAGER = 7;
    public const ROLE_COMMUNITY_MANAGER_PUBLIC = 8;
    public const ROLE_COMMUNITY_MANAGER_PRIVATE = 9;
    public const ROLE_SOLIDARY_MANAGER = 10;
    public const ROLE_SOLIDARY_VOLUNTEER = 11;
    public const ROLE_SOLIDARY_BENEFICIARY = 12;
    public const ROLE_COMMUNICATION_MANAGER = 13;
    public const ROLE_SOLIDARY_VOLUNTEER_CANDIDATE = 171;
    public const ROLE_SOLIDARY_BENEFICIARY_CANDIDATE = 172;

    /**
     * @var int The id of this item.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("authRead")
     */
    private $id;

    /**
     * @var string The type of the item : 1 = role, 2 = item.
     *
     * @ORM\Column(type="integer", length=1)
     * @Groups({"authRead","authWrite"})
     */
    private $type;

    /**
     * @var string The name of the item.
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"authRead","authWrite"})
     */
    private $name;

    /**
     * @var string The description of the item.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"authRead","authWrite"})
     */
    private $description;

    /**
     * @var AuthRule The rule associated with the item
     *
     * @ORM\ManyToOne(targetEntity="\App\Auth\Entity\AuthRule")
     * @Groups({"authRead","authWrite"})
     * @MaxDepth(1)
     */
    private $authRule;

    /**
     * @var ArrayCollection|null The parents of this item.
     *
     * @ORM\ManyToMany(targetEntity="App\Auth\Entity\AuthItem", mappedBy="items")
     * @Groups({"read","write"})
     */
    private $parents;

    /**
     * @var ArrayCollection|null The children of this item.
     *
     * @ORM\ManyToMany(targetEntity="App\Auth\Entity\AuthItem", inversedBy="parents")
     * @ORM\JoinTable(name="auth_item_child",
     *      joinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")}
     *      )
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->parents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getAuthRule(): ?AuthRule
    {
        return $this->authRule;
    }

    public function setAuthRule(?AuthRule $authRule): self
    {
        $this->authRule = $authRule;

        return $this;
    }

    public function getItems()
    {
        return $this->items->getValues();
    }

    public function addItem(AuthItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
        }

        return $this;
    }

    public function removeItem(AuthItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    public function getParents()
    {
        return $this->parents->getValues();
    }

    public function addParent(AuthItem $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents[] = $parent;
        }

        return $this;
    }

    public function removeParent(AuthItem $parent): self
    {
        if ($this->parents->contains($parent)) {
            $this->parents->removeElement($parent);
        }

        return $this;
    }
}
