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

namespace App\Solidary\Entity\SolidaryResult;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary Result Carpool
 *
 * ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidarySearch"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidarySearch"}}
 *      },
 *      collectionOperations={
 *          "get"
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */

class SolidaryResultCarpool
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidarySearch"})
     */
    private $id;

    /**
     * @var string Name of the author
     * @Groups({"readSolidarySearch"})
     */
    private $author;

    /**
    * @var int Id of the author
    * @Groups({"readSolidarySearch"})
    */
    private $authorId;

    /**
     * @var string Journey's origin
     * @Groups({"readSolidarySearch"})
     */
    private $origin;

    /**
     * @var string Journey's destination
     * @Groups({"readSolidarySearch"})
     */
    private $destination;

    /**
     * @var array Journey's schedule
     * @Groups({"readSolidarySearch"})
     */
    private $schedule;

    /**
     * @var \DateTimeInterface Journey's date from
     * @Groups({"readSolidarySearch"})
     */
    private $date;

    /**
     * @var int The proposal frequency (1 = punctual; 2 = regular)
     * Based on Criteria's constants
     * @Groups({"readSolidarySearch"})
     */
    private $frequency;

    /**
     * @var int The role (Driver : 1, Passenger : 2, Both : 3)
     * Based on Ad's constants
     * @Groups({"readSolidarySearch"})
     */
    private $role;

    /**
     * @var boolean If it's a solidary exclusive proposal
     * @Groups({"readSolidarySearch"})
     */
    private $solidaryExlusive;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getSchedule(): ?array
    {
        return $this->schedule;
    }

    public function setSchedule(array $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function isSolidaryExlusive(): ?bool
    {
        return $this->solidaryExlusive;
    }

    public function setSolidaryExlusive(bool $solidaryExlusive): self
    {
        $this->solidaryExlusive = $solidaryExlusive;

        return $this;
    }
}
