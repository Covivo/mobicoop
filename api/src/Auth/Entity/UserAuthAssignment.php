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

namespace App\Auth\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Auth\Entity\AuthItem;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Geography\Entity\Territory;
use App\User\Entity\User;

/**
 * User auth assignment
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"authRead"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"authWrite"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class UserAuthAssignment
{
    /**
     * @var int The id of this assignment.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("authRead")
     */
    private $id;

    /**
     * @var User The user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="userAuthAssignments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"authRead","authWrite","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var AuthItem The auth item.
     *
     * @ORM\ManyToOne(targetEntity="\App\Auth\Entity\AuthItem")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"authRead","authWrite","write"})
     */
    private $authItem;

    /**
     * @var Territory|null The territory associated with the assignment.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"authRead","authWrite","write"})
     */
    private $territory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAuthItem(): AuthItem
    {
        return $this->authItem;
    }

    public function setAuthItem(AuthItem $authItem): self
    {
        $this->authItem = $authItem;

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
