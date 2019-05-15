<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Right\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A role.
 *
 * @ORM\Entity
 * @UniqueEntity("name")
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title", "name", "parent"}, arguments={"orderParameterName"="order"})
 */
class Role
{
    // default role
    const DEFAULT_ROLE = 1;
    
    /**
     * @var int The id of this role.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
        
    /**
     * @var string The title of the role (user friendly name).
     *
     * @ORM\Column(type="string", length=45)
     * @Groups({"read","write"})
     */
    private $title;
    
    /**
     * @var string|null The name of the role.
     *
     * @ORM\Column(type="string", length=45)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var Role|null Parent role.
     *
     * @ORM\OneToOne(targetEntity="\App\Right\Entity\Role", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","write"})
     */
    private $parent;

    /**
     * @var ArrayCollection|null The rights of the role.
     *
     * @ORM\ManyToMany(targetEntity="\App\Right\Entity\Right")
     * @Groups({"read","write"})
     */
    private $rights;

    public function __construct()
    {
        $this->rights = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
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
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name): self
    {
        $this->name = $name;
        
        return $this;
    }

    public function getParent(): ?Role
    {
        return $this->parent;
    }
    
    public function setParent(?Role $parent): self
    {
        $this->parent = $parent;
        
        return $this;
    }

    public function getRights()
    {
        return $this->rights->getValues();
    }
    
    public function addRight(Right $right): self
    {
        if (!$this->rights->contains($right)) {
            $this->rights[] = $right;
        }
        
        return $this;
    }
    
    public function removeRight(Right $right): self
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
        }
        
        return $this;
    }
}
