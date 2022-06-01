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
 */

namespace App\Auth\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Geography\Entity\Territory;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * User auth assignment.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
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
 */
class UserAuthAssignment
{
    /**
     * @var int the id of this assignment
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("authRead")
     */
    private $id;

    /**
     * @var User the user
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="userAuthAssignments")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"authRead","authWrite","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var AuthItem the auth item
     *
     * @ORM\ManyToOne(targetEntity="\App\Auth\Entity\AuthItem")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"authRead","authWrite","write"})
     */
    private $authItem;

    /**
     * @var null|Territory the territory associated with the assignment
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Territory")
     * @ORM\JoinColumn(onDelete="CASCADE")
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
