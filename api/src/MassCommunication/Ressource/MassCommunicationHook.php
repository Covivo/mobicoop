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

namespace App\MassCommunication\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A sendinBlue hook when there is an unsubscription.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "unsubscribeHook"={
 *              "path"="/campaigns/unsubscribe",
 *              "method"="POST",
 *              "denormalization_context"={"groups"={"write"}},
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class MassCommunicationHook
{
    public const DEFAULT_ID = '999999999999';

    /**
     * @var int
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"read", "write"})
     */
    private $id;

    /**
     * @var null|string
     *
     * @Groups({"read", "write"})
     */
    private $event;

    /**
     * @var null|string
     *
     * @Groups({"read", "write"})
     */
    private $email;

    /**
     * @var null|string
     *
     * @Groups({"read", "write"})
     */
    private $date;

    /**
     * @var null|string
     *
     * @Groups({"read", "write"})
     */
    private $sendingIp;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
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

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(?string $event): self
    {
        $this->event = $event;

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

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSendingIp(): ?string
    {
        return $this->sendingIp;
    }

    public function setSendingIp(?string $sendingIp): self
    {
        $this->sendingIp = $sendingIp;

        return $this;
    }
}
