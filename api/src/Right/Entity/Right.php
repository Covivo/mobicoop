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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;

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
     * @var int The type of the right (1=item; 2=group).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $type;
    
    /**
     * @var string The name of the right.
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var Right|null Parent right.
     *
     * @ORM\OneToOne(targetEntity="\App\Right\Entity\Right", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","write"})
     */
    private $parent;

    public function __construct()
    {
        $this->groups = new Collection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
        
    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
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

    public function getParent(): ?Right
    {
        return $this->parent;
    }
    
    public function setParent(?Right $parent): self
    {
        $this->parent = $parent;
        
        return $this;
    }

}
