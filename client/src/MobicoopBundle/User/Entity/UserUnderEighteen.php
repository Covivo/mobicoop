<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class UserUnderEighteen implements ResourceInterface, \JsonSerializable
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this Block
     *
     * @Groups({"get"})
     */
    private $id;

    /**
     * @var string The User parentalConsentUuid
     *
     * @Groups({"post", "get"})
     */
    private $uuid;

    /**
     * @var string The User parentalConsentToken
     *
     * @Groups({"post"})
     */
    private $token;

    /**
     * @var string The User givenname
     *
     * @Groups({"get"})
     */
    private $givenName;

    /**
     * @var string The User familyname
     *
     * @Groups({"get"})
     */
    private $familyName;

    /**
     * @var null|\DateTimeInterface Date of the parental consent
     *
     * @Groups({"get"})
     */
    private $parentalConsentDate;

    /**
     * @var null|int The gender of the user (1=female, 2=male, 3=nc)
     *
     * @Groups({"get"})
     */
    private $gender;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getParentalConsentDate(): ?\DateTimeInterface
    {
        return $this->parentalConsentDate;
    }

    public function setParentalConsentDate(?\DateTimeInterface $parentalConsentDate): self
    {
        $this->parentalConsentDate = $parentalConsentDate;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'uuid' => $this->getUuid(),
            'token' => $this->getToken(),
            'givenName' => $this->getGivenName(),
            'familyName' => $this->getFamilyName(),
            'gender' => $this->getGender(),
            'parentalConsentDate' => $this->getParentalConsentDate(),
        ];
    }
}
