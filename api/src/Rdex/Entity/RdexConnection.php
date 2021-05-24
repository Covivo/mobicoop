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

namespace App\Rdex\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use App\Rdex\Controller\ConnectionController;
use App\Rdex\Entity\RdexConnectionUser;

/**
 * An RDEX Connection (conctact a user on a rdex platform)
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "formats"={"xml", "jsonld", "json"},
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *       collectionOperations={
 *          "get",
 *          "post"={
 *              "path"="/connections",
 *              "controller"=ConnectionController::class,
 *              "swagger_context" = {
 *                  "summary"="Contact a user using RDEX protocol",
 *                  "tags"={"RDEX"},
 *                  "parameters" = {
 *                      {
 *                          "name" = "timestamp",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The timestamp"
 *                      },
 *                      {
 *                          "name" = "apikey",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The api key"
 *                      },
 *                      {
 *                          "name" = "p[driver][uuid]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "int",
 *                          "description" = "Uuid of the driver"
 *                      },
 *                      {
 *                          "name" = "p[driver][state]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "enum" = {"sender","recipient"},
 *                          "description" = "If the driver is the sender or the recipient of this contact"
 *                      },
 *                      {
 *                          "name" = "p[passenger][uuid]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "int",
 *                          "description" = "Uuid of the passenger"
 *                      },
 *                      {
 *                          "name" = "p[passenger][state]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "enum" = {"sender","recipient"},
 *                          "description" = "If the passenger is the sender or the recipient of this contact"
 *                      },
 *                      {
 *                          "name" = "p[journeys][uuid]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "int",
 *                          "description" = "Uuid of the journey"
 *                      },
 *                      {
 *                          "name" = "signature",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The signature"
 *                      },
 *                  },
 *              },
 *          }
 *      },
 *      itemOperations={
 *          "get",
*        }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexConnection
{
    const MAX_LENGTH_DETAILS = 500;

    const STATE_RECIPIENT = "recipient";
    const STATE_SENDER = "sender";
    const AUTHORIZED_STATE = [self::STATE_SENDER, self::STATE_RECIPIENT];

    /**
     * @ApiProperty(identifier=true)
     *
     * @var string The uuid of the journey.
     *
     * @Groups("rdex")
     */
    private $uuid;

    /**
     * @var string The name of the operator.
     *
     * @Groups("rdex")
     */
    private $operator;

    /**
     * @var string The url of the site.
     *
     * @Groups("rdex")
     */
    private $origin;

    /**
     * @var RdexConnectionUser The driver.
     *
     * @Groups("rdex")
     */
    private $driver;

    /**
     * @var RdexConnectionUser The passenger.
     *
     * @Groups("rdex")
     */
    private $passenger;

    /**
     * @var int The uuids of the journey.
     * Yes, there a 's' in the spec but we only take one... don't ask
     * @Groups("rdex")
     */
    private $journeysId;

    /**
     * @var string The message.
     *
     * @Groups("rdex")
     */
    private $details;

    public function __construct($uuid=null)
    {
        (!is_null($uuid)) ? $this->uuid = $uuid : $this->uuid = -999999999;
    }

    public function getUuid(): int
    {
        return $this->uuid;
    }

    public function setUuid(int $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDriver(): RdexConnectionUser
    {
        return $this->driver;
    }

    public function setDriver(RdexConnectionUser $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getPassenger(): RdexConnectionUser
    {
        return $this->passenger;
    }

    public function setPassenger(RdexConnectionUser $passenger): self
    {
        $this->passenger = $passenger;

        return $this;
    }

    public function getJourneysId(): int
    {
        return $this->journeysId;
    }

    public function setJourneysId(int $journeysId): self
    {
        $this->journeysId = $journeysId;

        return $this;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }
}
