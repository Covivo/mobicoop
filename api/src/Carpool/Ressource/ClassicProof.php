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

namespace App\Carpool\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Entity\User;

/**
 * Carpooling : a proof for a classic ad.
 * Used to create a carpool proof for a classic ad (not a dynamic one, but "proofed" by a mobile app).
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readClassicProof"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeClassicProof"}},
 *          "validation_groups"={"writeClassicProof"}
 *      },
 *      collectionOperations={
 *          "post"={
 *              "method"="POST",
 *              "normalization_context"={"groups"={"writeClassicProof"}},
 *              "security_post_denormalize"="is_granted('carpool_proof_create',object)",
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
 *              "security"="is_granted('carpool_proof_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "read"=false,
 *              "normalization_context"={"groups"={"updateClassicProof"}},
 *              "denormalization_context"={"groups"={"updateClassicProof"}},
 *              "validation_groups"={"updateClassicProof"},
 *              "security"="is_granted('carpool_proof_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          },
 *          "cancel"={
 *              "method"="PUT",
 *              "path"="/classic_proofs/{id}/cancel",
 *              "read"=false,
 *              "normalization_context"={"groups"={"cancelClassicProof"}},
 *              "denormalization_context"={"groups"={"cancelClassicProof"}},
 *              "validation_groups"={"cancelClassicProof"},
 *              "security"="is_granted('carpool_proof_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Proofs"}
 *              }
 *          }
 *      }
 * )
 *
 */
class ClassicProof
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this classic ad proof.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readClassicProof","writeClassicProof","updateClassicProof","cancelClassicProof"})
     */
    private $id;

    /**
     * @var User|null The current user.
     *
     * @Groups("readClassicProof")
     */
    private $user;

    /**
     * @var string The latitude.
     *
     * @Groups({"writeClassicProof","updateClassicProof"})
     * @Assert\NotBlank(groups={"writeClassicProof","updateClassicProof"})
     */
    private $latitude;

    /**
     * @var string The longitude.
     *
     * @Groups({"writeClassicProof","updateClassicProof"})
     * @Assert\NotBlank(groups={"writeClassicProof","updateClassicProof"})
     */
    private $longitude;

    /**
     * @var int|null The ask id related to the proof.
     *
     * @Assert\NotBlank(groups={"writeClassicProof"})
     * @Groups("writeClassicProof")
     */
    private $askId;

    /**
     * @var int Proof status (0 = pending, 1 = sent to the register; 2 = error while sending to the register).
     * @Groups("cancelClassicProof")
     */
    private $status;

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

    public function getAskId(): ?int
    {
        return $this->askId;
    }

    public function setAskId(?int $askId): self
    {
        $this->askId = $askId;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
