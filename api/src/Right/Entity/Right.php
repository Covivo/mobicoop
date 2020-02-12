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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * A right.
 * Note : we change the name of the table to 'uright' to avoid sql errors as 'right' is a reserved word.
 *
 * @ORM\Entity
 * @ORM\Table(name="uright")
 * @UniqueEntity("name")
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(NumericFilter::class, properties={"type"})
 * @ApiFilter(OrderFilter::class, properties={"id", "type", "name", "parent"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 */
class Right
{
    const RIGHT_TYPE_ITEM = 1;
    const RIGHT_TYPE_GROUP = 2;
    
    /**
     * @var int The id of this right.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
            
    /**
     * @var string The name of the right.
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var string The description of the right.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $description;

    /**
     * @var ArrayCollection|null Child rights.
     *
     * @ORM\ManyToMany(targetEntity="\App\Right\Entity\Right")
     * @Groups({"read","write"})
     */
    private $parents;

    /**
     * @var ArrayCollection|null The roles having this right.
     *
     * @ORM\ManyToMany(targetEntity="\App\Right\Entity\Role", mappedBy="rights")
     * @Groups({"read","write"})
     */
    private $roles;

    /**
     * @var string The object related to the right.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $object;

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
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
    
    public function getParents()
    {
        return $this->parents;
    }

    public function addParent(Right $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents[] = $parent;
        }
        
        return $this;
    }
    
    public function removeParent(Right $parent): self
    {
        if ($this->parents->contains($parent)) {
            $this->parents->removeElement($parent);
        }
        
        return $this;
    }

    public function getRoles()
    {
        return $this->roles->getValues();
    }
    
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }
        
        return $this;
    }
    
    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
        
        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }
    
    public function setObject(?string $object): self
    {
        $this->object = $object;
        
        return $this;
    }
}
