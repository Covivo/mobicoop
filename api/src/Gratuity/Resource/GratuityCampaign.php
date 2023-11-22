<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Gratuity\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readGratuity"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeGratuity"}}
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('gratuity_list',object)",
 *              "swagger_context" = {
 *                  "summary"="Get the Gratuity Campaigns list of the instance",
 *                  "tags"={"Gratuity"}
 *               }
 *           },
 *           "post"={
 *             "security_post_denormalize"="is_granted('gratuity_create',object)",
 *              "swagger_context" = {
 *                  "summary"="Create a Gratuity Campaign",
 *                  "tags"={"Gratuity"}
 *              }
 *           },
 *       },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('gratuity_read',object)",
 *              "swagger_context" = {
 *                  "summary"="Get a Gratuity Campaign",
 *                  "tags"={"Gratuity"}
 *              }
 *          }
 *      }
 * )
 */
class GratuityCampaign
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The Campaign's id
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"readGratuity", "writeGratuity"})
     *
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var User The User who created the Campaign
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $user;

    /**
     * @var null|array the territories of this campaign (can be null or empty, it means that this campaign apply everywhere)
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $territories;

    /**
     * @var string Campaign's name. Mostly used for intern managment
     *
     * @Assert\NotBlank
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $name;

    /**
     * @var string Campaign's template. Related to a twig file
     *
     * @Assert\NotBlank
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $template;

    /**
     * @var string Campaign's status
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Campaign's start date
     *
     * @Assert\NotBlank
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $startDate;

    /**
     * @var \DateTimeInterface Campaign's end date
     *
     * @Assert\NotBlank
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $endDate;

    /**
     * @var \DateTimeInterface creation date of the user
     *
     * @Groups({"readGratuity"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface validation date of the user
     *
     * @Groups({"readGratuity"})
     */
    private $updatedDate;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->territories = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getTerritories()
    {
        return $this->territories;
    }

    public function setTerritories(?array $territories): self
    {
        $this->territories = $territories;

        return $this;
    }

    public function pushTerritory(?int $territory): self
    {
        $this->territories[] = $territory;

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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }
}
