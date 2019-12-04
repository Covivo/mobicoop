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

namespace Mobicoop\Bundle\MobicoopBundle\Communication\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A contact message.
 */
class Contact implements ResourceInterface
{
    const TYPES = [
        'support' => 0,
        'contact' => 1
    ];

    /**
     * @var int The id of this contact.
     * @Groups({"post"})
     */
    private $id;

    /**
     * @var string|null The iri of this contact.
     *
     * @Groups({"post"})
     */
    private $iri;

    /**
     * @var string|null The first name of the contacting person.
     *
     * @Groups({"post"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the contacting person.
     *
     * @Groups({"post"})
     */
    private $familyName;

    /**
     * @var string The email of the contacting person.
     *
     * @Groups({"post"})
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string|null The demand of the contacting person.
     *
     * @Groups({"post"})
     */
    private $demand;

    /**
     * @var string The message from the contacting person.
     *
     * @Groups({"post"})
     *
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @var \DateTime The date when the message is sent.
     */
    private $datetime;

    /**
     * @var int|null The type of contact
     *
     * @Groups({"post"})
     */
    private $type;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    /**
     * @return null|string
     */
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    /**
     * @param null|string $givenName
     * @return Contact
     */
    public function setGivenName(?string $givenName): Contact
    {
        $this->givenName = $givenName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    /**
     * @param null|string $familyName
     * @return Contact
     */
    public function setFamilyName(?string $familyName): Contact
    {
        $this->familyName = $familyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail(string $email): Contact
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDemand(): ?string
    {
        return $this->demand;
    }

    /**
     * @param null|string $demand
     * @return Contact
     */
    public function setDemand(?string $demand): Contact
    {
        $this->demand = $demand;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Contact
     */
    public function setMessage(string $message): Contact
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    /**
     * @param \DateTime $datetime
     * @return Contact
     */
    public function setDatetime(\DateTime $datetime): Contact
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     * @return Contact
     */
    public function setType(?int $type): self
    {
        $this->type = $type;
        return $this;
    }
}
