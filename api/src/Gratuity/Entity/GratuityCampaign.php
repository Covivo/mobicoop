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

namespace App\Gratuity\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Geography\Entity\Territory;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Gamification : A Badge that can be won/achieved by a User.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
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
 *                  "summary"="Get the badges list of the instance",
 *                  "tags"={"Gratuity"}
 *               }
 *           }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('gratuity_read',object)",
 *              "swagger_context" = {
 *                  "summary"="Get a Badge",
 *                  "tags"={"Gratuity"}
 *              }
 *          }
 *      }
 * )
 */
class GratuityCampaign
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The Campaign's id
     *
     * @ApiProperty(identifier=true)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"readGratuity", "writeGratuity"})
     *
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var User The User who created the Campaign
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="gratuityCampaigns")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $user;

    /**
     * @var null|ArrayCollection the territories of this campaign (can be null, it means that this campaign apply everywhere)
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory")
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $territories;

    /**
     * @var string Campaign's name. Mostly used for intern managment
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $name;

    /**
     * @var string Campaign's template. Related to a twig file
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $template;

    /**
     * @var string Campaign's status
     *
     * @ORM\Column(type="integer", length=255)
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Campaign's start date
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $startDate;

    /**
     * @var \DateTimeInterface Campaign's end date
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({"readGratuity", "writeGratuity"})
     */
    private $endDate;

    /**
     * @var \DateTimeInterface creation date of the user
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({"readGratuity"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface validation date of the user
     *
     * @ORM\Column(type="datetime", nullable=true)
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
        $this->territories = new ArrayCollection();
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
        return $this->territories->getValues();
    }

    public function addTerritory(Territory $territory): self
    {
        if (!$this->territories->contains($territory)) {
            $this->territories[] = $territory;
        }

        return $this;
    }

    public function removeTerritory(Territory $territory): self
    {
        if ($this->territories->contains($territory)) {
            $this->territories->removeElement($territory);
        }

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

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
