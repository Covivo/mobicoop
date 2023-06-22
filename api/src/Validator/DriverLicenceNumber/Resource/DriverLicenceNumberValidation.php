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

namespace App\Validator\DriverLicenceNumber\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A DriverLicenceNumber validation.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readDriverLicenceNumberValidation"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeDriverLicenceNumberValidation"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "post"={
 *              "denormalization_context"={"groups"={"writeDriverLicenceNumberValidation"}},
 *              "normalization_context"={"groups"={"readDriverLicenceNumberValidation"}},
 *              "read"="false",
 *              "security_post_denormalize"="is_granted('driver_licence_number_validation',object)",
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
class DriverLicenceNumberValidation
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"readDriverLicenceNumberValidation"})
     */
    private $id;

    /**
     * @var string The driver licence number to validate
     *
     * @Assert\NotBlank
     *
     * @Groups({"readDriverLicenceNumberValidation","writeDriverLicenceNumberValidation"})
     */
    private $driverLicenceNumber;

    /**
     * @var bool If the driver licence number is valid
     *
     * @Groups({"readDriverLicenceNumberValidation"})
     */
    private $valid;

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

    public function getDriverLicenceNumber(): ?string
    {
        return $this->driverLicenceNumber;
    }

    public function setDriverLicenceNumber(string $driverLicenceNumber): self
    {
        $this->driverLicenceNumber = $driverLicenceNumber;

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
}
