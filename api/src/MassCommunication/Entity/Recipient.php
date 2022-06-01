<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\MassCommunication\Entity;

/**
 * A campaign recipient.
 */
class Recipient
{
    /**
     * @var null|string the first name of the recipient
     */
    private $givenName;

    /**
     * @var null|string the family name of the recipient
     */
    private $familyName;

    /**
     * @var null|string the email of the recipient
     */
    private $email;

    /**
     * @var null|string the telephone of the recipient
     */
    private $telephone;

    /**
     * @var null|string the unsubscribe token of the recipient
     */
    private $unsubscribeToken;

    public function __construct(?string $email = null, ?string $givenName = null, ?string $familyName = null, ?string $telephone = null, ?string $unsubscribeToken = null)
    {
        $this->setEmail($email);
        $this->setGivenName($givenName);
        $this->setFamilyName($familyName);
        $this->setTelephone($telephone);
        $this->setUnsubscribeToken($unsubscribeToken);
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getUnsubscribeToken(): ?string
    {
        return $this->unsubscribeToken;
    }

    public function setUnsubscribeToken(?string $unsubscribeToken): self
    {
        $this->unsubscribeToken = $unsubscribeToken;

        return $this;
    }
}
