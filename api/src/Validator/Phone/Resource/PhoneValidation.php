<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Validator\Phone\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Phone validation.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPhoneValidation"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePhoneValidation"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "post"={
 *              "denormalization_context"={"groups"={"writePhoneValidation"}},
 *              "normalization_context"={"groups"={"readPhoneValidation"}},
 *              "read"="false",
 *              "security_post_denormalize"="is_granted('phone_number_validation',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PhoneValidation
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPhoneValidation"})
     */
    private $id;

    /**
     * @var string The phone number to validate
     *
     * @Assert\NotBlank
     * @Groups({"readPhoneValidation","writePhoneValidation"})
     */
    private $phoneNumber;

    /**
     * @var bool If the phone number is valid
     *
     * @Groups({"readPhoneValidation"})
     */
    private $valid;

    /**
     * @var string Message (i.e. if the phone number is invalid)
     *
     * @Groups({"readPhoneValidation"})
     */
    private $message;

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

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function isValid(): ?bool
    {
        return (!is_null($this->valid)) ? $this->valid : false;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
