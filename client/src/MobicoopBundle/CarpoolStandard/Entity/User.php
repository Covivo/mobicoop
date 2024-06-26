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

namespace Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A User.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class User implements ResourceInterface, \JsonSerializable
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var null|int The id of this user
     *
     * @Groups({"get","post","put"})
     */
    private $id;

    /**
     * @var null|string The id of this user
     *
     * @Groups({"get","post","put"})
     */
    private $externalId;

    /**
     * @var string the operator identifier
     *
     * @Assert\NotBlank
     *
     * @Groups({"get","post","put"})
     */
    private $operator;

    /**
     * @var string User's alias
     *
     * @Assert\NotBlank
     *
     * @Groups({"get","post","put"})
     */
    private $alias;

    /**
     * @var null|string user's first name
     *
     * @Groups({"get","post","put"})
     */
    private $firstName;

    /**
     * @var null|string user's last name
     *
     * @Groups({"get","post","put"})
     */
    private $lastName;

    /**
     * @var null|int user's grade from 1 to 5
     *
     * @Groups({"get","post","put"})
     */
    private $grade;

    /**
     * @var null|string user's profile picture absolute URL
     *
     * @Groups({"get","post","put"})
     */
    private $picture;

    /**
     * @var null|string User's gender. [ F, M, O ] 'O' stands for 'Other'.
     *
     * @Groups({"get","post","put"})
     */
    private $gender;

    /**
     * @var null|bool
     *
     * @Groups({"get","post","put"})
     */
    private $verifiedIdentity;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(?int $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getVerifiedIdentity(): ?bool
    {
        return $this->verifiedIdentity;
    }

    public function setVerifiedIdentity(?bool $verifiedIdentity): self
    {
        $this->verifiedIdentity = $verifiedIdentity;

        return $this;
    }

    public function jsonSerialize()
    {
        return
        [
            'id' => $this->getId(),
            'externalId' => $this->getExternalId(),
            'operator' => $this->getOperator(),
            'alias' => $this->getAlias(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'grade' => $this->getGrade(),
            'picture' => $this->getPicture(),
            'gender' => $this->getGender(),
            'verifiedIdentity' => $this->getVerifiedIdentity(),
        ];
    }
}
