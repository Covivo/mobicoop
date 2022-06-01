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

namespace App\Communication\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Communication\Ressource\ContactType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
    public const DEFAULT_ID = 999999999999;

    public const SUPPORT_CONTACT = 0;
    public const SIMPLE_CONTACT = 1;

    public const SEND_TO = 'To';
    public const SEND_CC = 'Cc';
    public const SEND_BCC = 'Bcc';

    /**
     * @var int the id of this contact
     * @ApiProperty(identifier=true)
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var null|string the first name of the contacting person
     * @Groups({"write"})
     */
    private $givenName;

    /**
     * @var null|string the family name of the contacting person
     * @Groups({"write"})
     */
    private $familyName;

    /**
     * @var string the email of the contacting person
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Groups({"write"})
     */
    private $email;

    /**
     * @var null|string the demand of the contacting person
     * @Groups({"write"})
     */
    private $demand;

    /**
     * @var string the message from the contacting person
     *
     * @Assert\NotBlank()
     * @Groups({"write"})
     */
    private $message;

    /**
     * @var \DateTime the date when the message is sent
     * @Groups({"write"})
     */
    private $datetime;

    /**
     * @var null|ContactType The type of contact
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

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): Contact
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): Contact
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Contact
    {
        $this->email = $email;

        return $this;
    }

    public function getDemand(): ?string
    {
        return $this->demand;
    }

    public function setDemand(?string $demand): Contact
    {
        $this->demand = $demand;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Contact
    {
        $this->message = $message;

        return $this;
    }

    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTime $datetime): Contact
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getContactType(): ?int
    {
        return $this->contactType;
    }

    /**
     * @param null|int $contactType
     */
    public function setContactType(?ContactType $contactType): Contact
    {
        $this->contactType = $contactType;

        return $this;
    }
}
