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

namespace App\Communication\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Communication\Controller\ContactMessage;
use App\Communication\Ressource\ContactType;

/**
 * A contact message.
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *     },
 *     itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          },
 *     },
 *     collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/contacts",
 *              "security_post_denormalize"="is_granted('communication_contact',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          },
 *      },
 * )
 */
class Contact
{
    const DEFAULT_ID = 999999999999;

    const SUPPORT_CONTACT = 0;
    const SIMPLE_CONTACT = 1;

    const SEND_TO = 'To';
    const SEND_CC = 'Cc';
    const SEND_BCC = 'Bcc';

    /**
     * @var int The id of this contact.
     * @ApiProperty(identifier=true)
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var string|null The first name of the contacting person.
     * @Groups({"write"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the contacting person.
     * @Groups({"write"})
     */
    private $familyName;

    /**
     * @var string The email of the contacting person.
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Groups({"write"})
     */
    private $email;

    /**
     * @var string|null The demand of the contacting person.
     * @Groups({"write"})
     */
    private $demand;

    /**
     * @var string The message from the contacting person.
     *
     * @Assert\NotBlank()
     * @Groups({"write"})
     */
    private $message;

    /**
     * @var \DateTime The date when the message is sent.
     * @Groups({"write"})
     */
    private $datetime;

    /**
     * @var ContactType|null The type of contact
     * @Groups({"write"})
     */
    private $contactType;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
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
    public function getContactType(): ?ContactType
    {
        return $this->contactType;
    }

    /**
     * @param int|null $contactType
     * @return Contact
     */
    public function setContactType(?ContactType $contactType): self
    {
        $this->contactType = $contactType;
        return $this;
    }
}
