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
 */

namespace App\Carpool\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : a proof for a dynamic ad.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readDynamic"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeDynamic"}},
 *          "validation_groups"={"writeDynamic"}
 *      },
 *      collectionOperations={
 *          "post"={
 *              "method"="POST",
 *              "normalization_context"={"groups"={"writeDynamic"}},
 *              "security_post_denormalize"="is_granted('dynamic_proof_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          },
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('dynamic_proof_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "read"=false,
 *              "normalization_context"={"groups"={"updateDynamic"}},
 *              "denormalization_context"={"groups"={"updateDynamic"}},
 *              "validation_groups"={"updateDynamic"},
 *              "security"="is_granted('dynamic_proof_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          }
 *      }
 * )
 */
class DynamicProof
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of this dynamic ad proof
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $id;

    /**
     * @var null|User the current user
     *
     * @Groups("readDynamic")
     */
    private $user;

    /**
     * @var string the latitude
     *
     * @Groups({"writeDynamic","updateDynamic"})
     * @Assert\NotBlank(groups={"writeDynamic","updateDynamic"})
     */
    private $latitude;

    /**
     * @var string the longitude
     *
     * @Groups({"writeDynamic","updateDynamic"})
     * @Assert\NotBlank(groups={"writeDynamic","updateDynamic"})
     */
    private $longitude;

    /**
     * @var null|int the ask id related to the proof
     *
     * @Assert\NotBlank(groups={"writeDynamic"})
     * @Groups("writeDynamic")
     */
    private $dynamicAskId;

    /**
     * @var string Proof live status, as a 4 digits binary ABCD number (eg : 1101) :
     *             - A => passenger pickup proof (0/1)
     *             - B => driver pickup proof (0/1)
     *             - C => passenger dropoff proof (0/1)
     *             - D => driver dropoff proof (0/1)
     *
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $status;

    /**
     * @var null|string driver's phone unique id
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $driverPhoneUniqueId;

    /**
     * @var null|string passenger's phone unique id
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $passengerPhoneUniqueId;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function getDynamicAskId(): ?int
    {
        return $this->dynamicAskId;
    }

    public function setDynamicAskId(?int $dynamicAskId): self
    {
        $this->dynamicAskId = $dynamicAskId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDriverPhoneUniqueId(): ?string
    {
        return $this->driverPhoneUniqueId;
    }

    public function setDriverPhoneUniqueId(?string $driverPhoneUniqueId): self
    {
        $this->driverPhoneUniqueId = $driverPhoneUniqueId;

        return $this;
    }

    public function getPassengerPhoneUniqueId(): ?string
    {
        return $this->passengerPhoneUniqueId;
    }

    public function setPassengerPhoneUniqueId(?string $passengerPhoneUniqueId): self
    {
        $this->passengerPhoneUniqueId = $passengerPhoneUniqueId;

        return $this;
    }
}
