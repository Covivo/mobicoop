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

namespace App\Solidary\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Solutions for a Solidary
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidarySolution
{
    const DEFAULT_ID = 999999999999;
    const TRANSPORTER = 'transporter';
    const CARPOOLER = 'carpooler';

    /**
     * @var int $id The id of this solidary matching.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $id;

    /**
     * @var Solidary The solidary record.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Solidary", inversedBy="solidarySolutions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidary;

    /**
     * @var SolidaryMatching|null SolidaryMatching of this SolidarySolution
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryMatching", inversedBy="solidarySolution", cascade={"persist","remove"})
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryMatching;

    /**
     * @var SolidaryAsk The solidary Ask for this solidarySolution
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryAsk", mappedBy="solidarySolution")
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryAsk;

    /**
     * @var string A comment about the solidary matching.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $comment;

    /**
     * @var SolidarySolution|null The linked solidary solution for return trips.
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidarySolution", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @MaxDepth(1)
     */
    private $solidarySolutionLinked;

    /**
     * @var \DateTimeInterface Creation date of the solidary record.
     *
     * @ORM\Column(type="datetime")
     * @Groups("readSolidary")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solidary record.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("readSolidary")
     */
    private $updatedDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }

    public function setSolidary(?Solidary $solidary): self
    {
        $this->solidary = $solidary;

        return $this;
    }

    public function getSolidaryMatching(): ?SolidaryMatching
    {
        return $this->solidaryMatching;
    }

    public function setSolidaryMatching(?SolidaryMatching $solidaryMatching): self
    {
        $this->solidaryMatching = $solidaryMatching;

        return $this;
    }

    public function getSolidaryAsk(): ?SolidaryAsk
    {
        return $this->solidaryAsk;
    }

    public function setSolidaryAsk(?SolidaryAsk $solidaryAsk): self
    {
        $this->solidaryAsk = $solidaryAsk;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSolidarySolutionLinked(): ?self
    {
        return $this->solidarySolutionLinked;
    }

    public function setSolidarySolutionLinked(?self $solidarySolutionLinked): self
    {
        $this->solidarySolutionLinked = $solidarySolutionLinked;

        // set (or unset) the owning side of the relation if necessary
        $newSolidarySolutionLinked = $solidarySolutionLinked === null ? null : $this;
        if (!is_null($solidarySolutionLinked) && $newSolidarySolutionLinked !== $solidarySolutionLinked->getSolidarySolutionLinked()) {
            $solidarySolutionLinked->setSolidarySolutionLinked($newSolidarySolutionLinked);
        }

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
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
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
