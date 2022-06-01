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

namespace App\ExternalJourney\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An external connection (i.e. sending a message) to an ExternalJourneyProvider.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readExternalConnection"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeExternalConnection"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "post"={
 *              "security"="is_granted('external_connection_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalConnection
{
    public const DEFAULT_ID = 999999999999;

    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;
    public const ROLE_DRIVER_OR_PASSENGER = 3;

    public const STATUS_RECIPIENT = 'recipient';
    public const STATUS_SENDER = 'sender';

    /**
     * @var int Id of the ExternalConnection
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readExternalConnection"})
     */
    private $id;

    /**
     * @var string Provider of the external journey (provider key in providers.json configuration)
     * @Assert\NotBlank
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $provider;

    /**
     * @var int The role of the User who's sending the external connection
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $role;

    /**
     * @var string Uuid of the Carpooler targetted by the ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     * @Assert\NotBlank
     */
    private $carpoolerUuid;

    /**
     * @var string Uuid of the journey concerned by this ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     * @Assert\NotBlank
     */
    private $journeysUuid;

    /**
     * @var string Content of the ExternalConnection (i.e. the message)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $content;

    public function __construct(int $id = null)
    {
        $this->id = self::DEFAULT_ID;
        if (!is_null($id)) {
            $this->id = $id;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(?int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getCarpoolerUuid(): ?string
    {
        return $this->carpoolerUuid;
    }

    public function setCarpoolerUuid(?string $carpoolerUuid): self
    {
        $this->carpoolerUuid = $carpoolerUuid;

        return $this;
    }

    public function getJourneysUuid(): ?string
    {
        return $this->journeysUuid;
    }

    public function setJourneysUuid(?string $journeysUuid): self
    {
        $this->journeysUuid = $journeysUuid;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
