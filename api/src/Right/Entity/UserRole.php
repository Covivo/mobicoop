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
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Territory;
use App\User\Entity\User;

/**
 * A role granted to a user.
 * Additionnal properties could be added so we need this entity (could be useless without extra properties => if so it would be a 'classic' manytomany relation)
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class UserRole
{
    /**
     * @var int The id of this user role.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
        
    /**
     * @var User The user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="userRoles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $user;
    
    /**
     * @var Role The role.
     *
     * @ORM\ManyToOne(targetEntity="\App\Right\Entity\Role")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $role;

    /**
     * @var Territory|null The territory associated with the user role.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"read","write"})
     */
    private $territory;
    
    public function getId(): ?int
    {
        return $this->id;
    }
        
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;
        
        return $this;
    }

    public function getTerritory(): ?Territory
    {
        return $this->territory;
    }

    public function setTerritory(?Territory $territory): self
    {
        $this->territory = $territory;
        
        return $this;
    }
}
